<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Guards;

use function array_any;
use function array_filter;
use function array_merge;
use function count;
use function dirname;
use function explode;
use function file_get_contents;
use function filter_var;
use function implode;
use function in_array;
use function is_array;
use function is_dir;
use function is_file;
use function json_decode;
use function ltrim;
use function preg_match;
use function preg_match_all;
use function preg_replace;
use function scandir;
use function sort;
use function str_contains;
use function str_ends_with;
use function str_replace;
use function str_starts_with;
use function strtolower;
use function substr_count;
use function token_get_all;
use function trim;

/**
 * Repository guards, run in CI.
 *
 * Comments are read through PHP's own tokeniser, so a pattern named here in
 * code is never mistaken for the thing it looks for.
 */
final class Guards
{
    private const int MAX_LINES = 550;

    /**
     * @var list<string>
     */
    private const array SUPPRESSIONS = [
        '@phpstan-ignore',
        '@psalm-suppress',
        '@codeCoverageIgnore',
        '@SuppressWarnings',
        '@infection-ignore-all',
        '@phan-suppress',
        'phpcs:ignore',
        'phpcs:disable',
        'phpstan-baseline',
    ];

    /**
     * @var list<string>
     */
    private const array REASONING_OPENERS = [
        'because',
        'we ',
        'the reason',
        'this is why',
        'originally',
        'it turns out',
        'note that',
        'arguably',
    ];

    /**
     * A requirement identifier. It belongs in a commit trailer and a pull
     * request body, not beside the code it once explained.
     */
    private const string IDENTIFIER = '/\\b[A-Z][A-Z0-9]*-R\\d+\\b/';

    /**
     * A line that reads as a comment, including one this repository writes
     * into a file it generates.
     */
    private const string COMMENT_LINE = '~^\\s*(?://|\\*|/\\*|\\#)~';

    /**
     * @var list<string>
     */
    private const array SCANNED = ['src', 'tests', 'scripts'];

    /**
     * @var list<string>
     */
    private const array LOOPBACK_ONLY = ['src'];

    /**
     * The one file that may reach the network, and the one that may not.
     *
     * Vendoring is the act of fetching: it takes the artefact from an exact
     * revision and writes it down beside the copy, which is why there is a copy.
     * Generating reads what vendoring left. A generator that fetched would build
     * types from whatever the server serves now rather than from the revision
     * this package was built against, and whoever installed it would have been
     * told one and handed the other.
     */
    private const string VENDORING = 'scripts/contract-sync.php';

    private const string GENERATING = 'scripts/contract-generate.php';

    /**
     * The one place an address is made, and the checks that must run to hold it.
     *
     * A client refuses an address that is not on this machine, and that holds only
     * while there is one way to make one. `BaseUrl`'s constructor is private, so
     * every address goes through a named factory that checks it — a public
     * constructor, or a third factory that skipped the check, would be a client
     * talking wherever it was pointed.
     */
    /**
     * What is said of a file this cannot open.
     *
     * One sentence rather than four, because a check that cannot read what it is
     * about has failed in the same way wherever it happens, and four wordings of
     * that would read as four different problems.
     */
    private const string UNREADABLE = 'could not be read';

    private const string ADDRESS = 'src/Http/BaseUrl.php';

    /**
     * How many times an address may be constructed, which is once per factory.
     */
    private const int FACTORIES = 2;

    /**
     * The composer scripts that hold something, and what each holds.
     *
     * A check the pipeline stopped calling is a rule nobody is held to, and the
     * failure is silence: every run goes green and the artefact it guarded drifts.
     *
     * @var array<string, string>
     */
    private const array WIRED = [
        'contract:check' => 'regenerating the contract types produces no diff',
        'guards' => 'these checks run at all',
        'test:coverage' => 'every line is exercised',
        'analyse' => 'the analyser sees what the types claim',
    ];

    /**
     * How a script reaches somewhere else.
     */
    private const string REACHES = '~\\b(?:curl_[a-z_]+|fsockopen|stream_socket_client|file_get_contents)\\s*\\(\\s*[\'"]https?://|https?://~i';

    /**
     * Written by `composer contract:generate`, and proved by regeneration
     * producing no diff rather than by these checks.
     *
     * @var list<string>
     */
    private const array GENERATED = ['src/Generated'];

    /**
     * @var list<string>
     */
    private array $failures = [];

    public function __construct(private readonly string $root) {}

    public function run(): int
    {
        foreach (self::SCANNED as $directory) {
            $loopbackOnly = in_array($directory, self::LOOPBACK_ONLY, true);

            foreach ($this->phpFilesIn($this->root . '/' . $directory) as $file) {
                if ($this->isGenerated($file)) {
                    continue;
                }

                $this->inspect($file, $loopbackOnly);
            }
        }

        $this->checkGenerationStaysHere();
        $this->checkTheGateIsWiredIn();
        $this->checkAnAddressIsMadeOneWay();

        if ($this->failures === []) {
            echo "guards: every check passed\n";

            return 0;
        }

        foreach ($this->failures as $failure) {
            echo 'guards: ' . $failure . "\n";
        }

        echo 'guards: ' . count($this->failures) . " problems found\n";

        return 1;
    }

    private function isGenerated(string $file): bool
    {
        return array_any(self::GENERATED, fn(string $directory): bool => str_starts_with($file, $this->root . '/' . $directory . '/'));
    }

    private function inspect(string $file, bool $loopbackOnly): void
    {
        $source = file_get_contents($file);

        if ($source === false) {
            $this->fail($file, 0, self::UNREADABLE);

            return;
        }

        $this->checkLength($file, $source);

        foreach (token_get_all($source) as $token) {
            if (! is_array($token)) {
                continue;
            }

            if ($token[0] === T_COMMENT || $token[0] === T_DOC_COMMENT) {
                $this->checkComment($file, $token[2], $token[1]);
            }

            // A generator keeps the comments it writes in a string, where the
            // tokeniser sees no comment at all.
            if ($token[0] === T_ENCAPSED_AND_WHITESPACE || $token[0] === T_CONSTANT_ENCAPSED_STRING) {
                $this->checkIdentifiers($file, $token[2], $token[1], true);
            }

            if ($loopbackOnly) {
                $this->checkForRemoteHost($file, $token[2], $token[1]);
            }
        }
    }

    private function checkLength(string $file, string $source): void
    {
        $lines = substr_count($source, "\n") + 1;

        if ($lines > self::MAX_LINES) {
            $this->fail($file, $lines, 'holds ' . $lines . ' lines, over the limit of ' . self::MAX_LINES);
        }
    }

    private function checkComment(string $file, int $line, string $comment): void
    {
        foreach (self::SUPPRESSIONS as $suppression) {
            if (str_contains($comment, $suppression)) {
                $this->fail($file, $line, 'carries the suppression ' . $suppression);
            }
        }

        $this->checkIdentifiers($file, $line, $comment, false);

        $offset = 0;

        foreach (explode("\n", $comment) as $text) {
            $opener = strtolower(ltrim($text, " \t/*#"));

            foreach (self::REASONING_OPENERS as $reasoning) {
                if (str_starts_with($opener, $reasoning)) {
                    $this->fail($file, $line + $offset, 'opens a comment with "' . $reasoning . '"');
                }
            }

            $offset++;
        }
    }

    /**
     * @param bool $commentLinesOnly Read only the lines that look like comments.
     */
    private function checkIdentifiers(string $file, int $line, string $text, bool $commentLinesOnly): void
    {
        $offset = 0;

        foreach (explode("\n", $text) as $candidate) {
            $isComment = ! $commentLinesOnly || preg_match(self::COMMENT_LINE, $candidate) === 1;

            if ($isComment && preg_match(self::IDENTIFIER, $candidate) === 1) {
                $this->fail($file, $line + $offset, 'cites a requirement identifier in a comment');
            }

            $offset++;
        }
    }

    /**
     * Only vendoring reaches the network; generating reads what it left.
     *
     * What this reads is asserted before what it finds. A generator renamed out
     * from under the rule leaves it matching nothing, and that failure is
     * silence: the check goes on passing about a file it can no longer see.
     */
    private function checkGenerationStaysHere(): void
    {
        $generator = $this->root . '/' . self::GENERATING;

        foreach ([self::GENERATING, self::VENDORING] as $expected) {
            if (! is_file($this->root . '/' . $expected)) {
                $this->fail($this->root . '/' . $expected, 0, 'is named by this check and is not there');
            }
        }

        if (! is_file($generator)) {
            return;
        }

        $source = file_get_contents($generator);

        if ($source === false) {
            $this->fail($generator, 0, self::UNREADABLE);

            return;
        }

        foreach (explode("\n", $source) as $offset => $line) {
            if (preg_match(self::COMMENT_LINE, $line) === 1) {
                continue;
            }

            if (preg_match(self::REACHES, $line) === 1) {
                $this->fail($generator, $offset + 1, 'reaches the network; only ' . self::VENDORING . ' may');
            }
        }

    }

    /**
     * Every check that holds something is part of what CI runs.
     */
    private function checkTheGateIsWiredIn(): void
    {
        $manifest = $this->root . '/composer.json';
        $read = file_get_contents($manifest);

        if ($read === false) {
            $this->fail($manifest, 0, self::UNREADABLE . ', so what CI runs is unknown');

            return;
        }

        $declared = json_decode($read, true);
        // Narrowed a step at a time rather than cast in one: a manifest is somebody
        // else's JSON, and every one of these could be something other than what is
        // expected without the file being wrong in any way this check is about.
        $scripts = is_array($declared) ? ($declared['scripts'] ?? null) : null;
        $ci = is_array($scripts) ? ($scripts['ci'] ?? null) : null;
        $steps = array_filter(is_array($ci) ? $ci : [$ci], is_string(...));
        $pipeline = implode(' ', $steps);

        if ($pipeline === '') {
            $this->fail($manifest, 0, 'declares no `ci` script to read');

            return;
        }

        foreach (self::WIRED as $script => $holds) {
            if (! str_contains($pipeline, $script)) {
                $this->fail($manifest, 0, '`ci` no longer runs `' . $script . '`, which is what holds that ' . $holds);
            }
        }
    }

    /**
     * An address is made in one place, by factories that check it.
     */
    private function checkAnAddressIsMadeOneWay(): void
    {
        $path = $this->root . '/' . self::ADDRESS;

        if (! is_file($path)) {
            $this->fail($path, 0, 'is named by this check and is not there');

            return;
        }

        $source = file_get_contents($path);

        if ($source === false) {
            $this->fail($path, 0, self::UNREADABLE);

            return;
        }

        if (preg_match('~^\s*+private function __construct\(~m', $source) !== 1) {
            $this->fail($path, 0, 'the constructor is not private, so an address can be made without being checked');
        }

        $made = preg_match_all('~new self\(~', $source);

        if ($made !== self::FACTORIES) {
            $this->fail($path, 0, 'an address is made in ' . $made . ' places and there are ' . self::FACTORIES . ' factories, so one of them does not check it');
        }
    }

    private function checkForRemoteHost(string $file, int $line, string $text): void
    {
        $matches = [];

        if (preg_match_all('#https?://([^/\s"\'<>)]+)#i', $text, $matches) === 0) {
            return;
        }

        foreach ($matches[1] as $authority) {
            if (! $this->isLoopback($authority)) {
                $this->fail($file, $line, 'names the address ' . $authority . ', which is not on this machine');
            }
        }
    }

    private function isLoopback(string $authority): bool
    {
        $host = trim((string) preg_replace('/:\d*$/', '', $authority), '[]');

        if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false) {
            return str_starts_with($host, '127.');
        }

        return $host === '::1';
    }

    private function fail(string $file, int $line, string $problem): void
    {
        $this->failures[] = str_replace($this->root . '/', '', $file) . ':' . $line . ' ' . $problem;
    }

    /**
     * @return list<string>
     */
    private function phpFilesIn(string $directory): array
    {
        $entries = scandir($directory);

        if ($entries === false) {
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

exit(new Guards(dirname(__DIR__))->run());
