<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Mutation;

use function array_diff;
use function array_diff_assoc;
use function array_filter;
use function array_key_exists;
use function array_keys;
use function array_merge;
use function array_slice;
use function array_unique;
use function array_values;
use function chdir;
use function count;
use function dirname;
use function escapeshellarg;
use function fwrite;
use function implode;
use function in_array;
use function is_array;
use function is_dir;
use function is_string;
use function json_encode;
use function ksort;
use function passthru;
use function scandir;
use function sort;
use function sprintf;

use const STDERR;
use const STDOUT;

use function str_ends_with;
use function str_starts_with;
use function strlen;
use function substr;

/**
 * The mutation gate, over everything or over one shard of it.
 *
 * The gate is the slowest thing this repository runs and it is one process: the
 * suite once under coverage, then a run of the covering tests per mutant. None
 * of that work is shared between one source file and the next, so it splits —
 * and the floor of 100 is what makes splitting it faithful rather than merely
 * fast. A score of 100 means every mutant in the run died, so a set of files
 * scores 100 exactly when each of its parts does: no part can carry another and
 * none can be carried, in either direction. At any floor below 100 that stops
 * being true and a split would be measuring something else.
 *
 * A shard is a top-level directory of `src`, and the classes sitting directly in
 * `src` are one more. That boundary is the package's own: each directory is a
 * namespace and a single concern — the envelope, the event stream, the
 * transport, the clock — so a shard names something a reader already knows, and
 * a red `mutation (Http)` says where to look before a log is opened. It is also
 * the boundary nobody has to keep: the split is read off the tree on every run,
 * so a directory added tomorrow has a runner the day it appears rather than the
 * day somebody remembers a list.
 *
 * Every shard is measured against the whole. The files the shards cover are
 * compared with every file a whole run mutates, and a difference in either
 * direction stops the run before a single mutant is made. A split that quietly
 * mutates less is the one failure a sharded gate has that an unsharded one
 * cannot, and from the outside it looks like success: every runner green, over
 * fewer files.
 *
 * Three ways in, two of them for CI. `--list` prints the shard names as JSON,
 * which is what a workflow matrix reads; `--shard=<name>` runs one of them. No
 * argument runs the whole of `src` in one process, which is what `composer ci`
 * and anybody running this by hand gets, and it is the set the shards partition.
 */
final class Mutation
{
    /**
     * The client's own code, and everything a shard is cut from.
     */
    private const string SOURCE = 'src';

    /**
     * Written by `composer contract:generate` and proved by regeneration
     * producing no diff rather than by a test killing a mutant. It is left out
     * of a whole run and of every shard, by the same argument in both.
     */
    private const string GENERATED = 'src/Generated';

    /**
     * What the classes sitting directly in `src` are mutated under.
     *
     * Every other shard is named after a directory, so this is the one name
     * that could be claimed twice. A directory of this name is refused below
     * rather than merged into it, since a merge would leave two different sets
     * of files answering to one runner and one check.
     */
    private const string ROOT = 'Root';

    /**
     * The score a run must reach, whole or sharded.
     *
     * The number is the split's warrant as much as the gate's: at 100 a shard
     * passes exactly when the files in it would have passed inside a whole run.
     */
    private const int FLOOR = 100;

    /**
     * What a whole run is held to beyond the floor: it must make a mutant.
     *
     * `src` producing none at all, while the suite covers every line of it, is
     * the gate measuring nothing, and a score of zero over zero mutants is the
     * only shape that failure has.
     */
    private const string EVERY_MUTANT = '';

    /**
     * What a shard is allowed that a whole run is not.
     *
     * A directory of interfaces and constants is still a directory, and it
     * holds nothing a mutator can change. The score of a run that made no
     * mutant is zero, which at this floor would fail as though a mutant had
     * escaped that was never made. The floor itself is untouched: every mutant
     * a shard does make must still die.
     */
    private const string OR_NO_MUTANT = ' --ignore-min-score-on-zero-mutations';

    /**
     * @var list<string>
     */
    private array $problems = [];

    /**
     * @param list<string> $given
     */
    public function __construct(private readonly string $root, private readonly array $given) {}

    public function run(): int
    {
        $status = $this->act();

        foreach ($this->problems as $problem) {
            fwrite(STDERR, 'mutation: ' . $problem . "\n");
        }

        return $this->problems === [] ? $status : 1;
    }

    /**
     * Whichever of the three ways in was asked for.
     */
    private function act(): int
    {
        // Pest resolves a path against the working directory, and this names
        // its own against the repository root. Entering it here means an
        // invocation from elsewhere mutates the same set rather than a smaller
        // one, or nothing at all.
        if (! chdir($this->root)) {
            $this->problems[] = sprintf('%s could not be entered, so no path here resolves.', $this->root);

            return 1;
        }

        $shards = $this->shards();

        if ($this->problems !== []) {
            return 1;
        }

        return $this->asked($shards);
    }

    /**
     * @param array<string, list<string>> $shards
     */
    private function asked(array $shards): int
    {
        if (in_array('--list', $this->given, true)) {
            return $this->listShards(array_keys($shards));
        }

        $named = $this->argument('--shard=');

        return $named === null
            ? $this->mutate(self::SOURCE, $this->mutable(), self::EVERY_MUTANT)
            : $this->oneShard($shards, $named);
    }

    /**
     * @param array<string, list<string>> $shards
     */
    private function oneShard(array $shards, string $named): int
    {
        if (array_key_exists($named, $shards)) {
            return $this->mutate($named, $shards[$named], self::OR_NO_MUTANT);
        }

        $this->problems[] = sprintf(
            'there is no shard called %s. A runner naming one that is gone is a runner that passes '
            . 'having mutated nothing, and the matrix is built by `--list` on this same commit, so the '
            . 'two disagree. The shards here are: %s.',
            $named,
            implode(', ', array_keys($shards)),
        );

        return 1;
    }

    /**
     * Every shard, by name, with the files it answers for.
     *
     * @return array<string, list<string>>
     */
    private function shards(): array
    {
        $entries = $this->topLevel();
        $shards = $this->directoryShards($entries);
        $atTheRoot = $this->rootFiles($entries);

        if ($atTheRoot !== []) {
            $shards[self::ROOT] = $atTheRoot;
        }

        ksort($shards);

        $this->checkTheShardsAreTheWhole($shards);

        return $shards;
    }

    /**
     * What sits directly in `src`, directories and files alike.
     *
     * @return list<string>
     */
    private function topLevel(): array
    {
        $entries = scandir(self::SOURCE);

        if ($entries === false) {
            $this->problems[] = sprintf('%s could not be read, so there is nothing to shard.', self::SOURCE);

            return [];
        }

        return array_values(array_filter(
            $entries,
            static fn(string $entry): bool => $entry !== '.' && $entry !== '..',
        ));
    }

    /**
     * A shard per top-level directory, named after it.
     *
     * @param list<string> $entries
     *
     * @return array<string, list<string>>
     */
    private function directoryShards(array $entries): array
    {
        $shards = [];

        foreach ($entries as $entry) {
            $path = self::SOURCE . '/' . $entry;

            if (! is_dir($path) || $path === self::GENERATED) {
                continue;
            }

            if ($entry === self::ROOT) {
                $this->problems[] = sprintf(
                    '%s is a directory and %s is also the name the classes directly in %s run under. '
                    . 'One name cannot answer for two sets of files; rename the directory or rename the shard.',
                    $path,
                    self::ROOT,
                    self::SOURCE,
                );
            }

            $found = $this->phpFilesIn($path);

            // A directory holding no PHP file is no shard. Said out loud rather
            // than passed over, since a runner given nothing to mutate and a
            // runner that killed every mutant print the same colour.
            if ($found === []) {
                fwrite(STDERR, sprintf("mutation: %s holds no PHP file, so no shard is cut from it.\n", $path));

                continue;
            }

            $shards[$entry] = $found;
        }

        return $shards;
    }

    /**
     * The classes sitting directly in `src`, which are one shard between them.
     *
     * @param list<string> $entries
     *
     * @return list<string>
     */
    private function rootFiles(array $entries): array
    {
        $found = [];

        foreach ($entries as $entry) {
            $path = self::SOURCE . '/' . $entry;

            if (! is_dir($path) && str_ends_with($entry, '.php')) {
                $found[] = $path;
            }
        }

        return $found;
    }

    /**
     * The shards cover every file a whole run mutates, once each.
     *
     * This is the check the split exists under. A top-level directory nobody
     * accounted for, a name claimed twice, a walk that stopped early — each of
     * them ends with a green gate over fewer files, which is the one outcome a
     * mutation score cannot report on, since the mutants that were never made
     * are the ones that would have escaped.
     *
     * @param array<string, list<string>> $shards
     */
    private function checkTheShardsAreTheWhole(array $shards): void
    {
        $covered = [];

        foreach ($shards as $files) {
            $covered = array_merge($covered, $files);
        }

        $mutable = $this->mutable();

        $missing = array_values(array_diff($mutable, $covered));
        $foreign = array_values(array_diff($covered, $mutable));
        $twice = array_values(array_diff_assoc($covered, array_unique($covered)));

        if ($missing !== []) {
            $this->problems[] = sprintf(
                'a whole run mutates these files and no shard does: %s',
                implode(', ', $missing),
            );
        }

        if ($foreign !== []) {
            $this->problems[] = sprintf(
                'a shard claims these files and a whole run does not mutate them: %s',
                implode(', ', $foreign),
            );
        }

        if ($twice !== []) {
            $this->problems[] = sprintf(
                'these files are claimed by more than one shard: %s',
                implode(', ', $twice),
            );
        }
    }

    /**
     * Every file a whole run mutates.
     *
     * @return list<string>
     */
    private function mutable(): array
    {
        return array_values(array_filter(
            $this->phpFilesIn(self::SOURCE),
            static fn(string $file): bool => ! str_starts_with($file, self::GENERATED . '/'),
        ));
    }

    /**
     * The shard names, as the JSON a workflow matrix reads.
     *
     * @param list<string> $names
     */
    private function listShards(array $names): int
    {
        $listed = json_encode($names);

        if ($listed === false) {
            $this->problems[] = 'the shard names could not be written as JSON.';

            return 1;
        }

        fwrite(STDOUT, $listed . "\n");

        return 0;
    }

    /**
     * Mutate a set of files at the floor, and answer with what Pest answered.
     *
     * The file list is passed a file at a time rather than as the directory it
     * came from, so what Pest walks is exactly what the check above compared
     * against the whole — one walk, not two that could disagree.
     *
     * `--everything` keeps a run that names paths mutating all of them rather
     * than only classes a test declared it covers, and `--covered-only` keeps
     * it off lines no test reaches. Both hold in a shard for the reason they
     * hold in a whole run, and neither depends on the split: every shard runs
     * the whole suite for its coverage, so the lines that may be mutated in a
     * file are the same wherever that file is run.
     *
     * @param list<string> $files
     */
    private function mutate(string $what, array $files, string $allowance): int
    {
        if ($files === []) {
            $this->problems[] = sprintf('%s holds no file to mutate, and a run over nothing is a pass over nothing.', $what);

            return 1;
        }

        fwrite(STDOUT, sprintf("\nMutation at %d%% over %s, %d files:\n", self::FLOOR, $what, count($files)));

        foreach ($files as $file) {
            fwrite(STDOUT, '  ' . $file . "\n");
        }

        $command = sprintf(
            'vendor/bin/pest --mutate --everything --covered-only --ignore=%s --min=%d%s --path=%s',
            escapeshellarg(self::GENERATED),
            self::FLOOR,
            $allowance,
            escapeshellarg(implode(',', $files)),
        );

        passthru($command, $status);

        return $status === 0 ? 0 : 1;
    }

    /**
     * The value of a `--name=` argument, or null where it was not given.
     *
     * Read off the arguments rather than through `getopt()`, which stops at the
     * first argument it does not know and would drop whatever composer passes
     * through after `--`.
     */
    private function argument(string $prefix): ?string
    {
        foreach ($this->given as $argument) {
            if (str_starts_with($argument, $prefix)) {
                return substr($argument, strlen($prefix));
            }
        }

        return null;
    }

    /**
     * Every PHP file under a directory, at any depth, in a settled order.
     *
     * @return list<string>
     */
    private function phpFilesIn(string $directory): array
    {
        $entries = scandir($directory);

        if ($entries === false) {
            $this->problems[] = sprintf('%s could not be read.', $directory);

            return [];
        }

        $found = [];

        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $path = $directory . '/' . $entry;

            if (is_dir($path)) {
                $found = array_merge($found, $this->phpFilesIn($path));

                continue;
            }

            if (str_ends_with($entry, '.php')) {
                $found[] = $path;
            }
        }

        sort($found);

        return $found;
    }
}

/** @var mixed $arguments */
$arguments = $_SERVER['argv'] ?? [];

$given = array_values(array_filter(is_array($arguments) ? $arguments : [], is_string(...)));

exit(new Mutation(dirname(__DIR__), array_slice($given, 1))->run());
