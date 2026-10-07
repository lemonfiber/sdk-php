<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Scripts;

require_once __DIR__ . '/GeneratedSource.php';
require_once __DIR__ . '/KeyCallable.php';
require_once __DIR__ . '/LineCap.php';
require_once __DIR__ . '/References.php';
require_once __DIR__ . '/Refusals.php';
require_once __DIR__ . '/SchemaTypes.php';
require_once __DIR__ . '/ShapePlan.php';

use function array_key_exists;
use function array_keys;
use function count;
use function dirname;
use function file_get_contents;
use function file_put_contents;
use function fwrite;
use function glob;
use function implode;
use function is_array;
use function is_dir;
use function is_file;
use function is_int;
use function is_string;
use function json_decode;

use JsonException;

use function ksort;
use function mkdir;
use function preg_match;
use function preg_split;
use function sprintf;
use function strtolower;
use function trim;
use function ucfirst;

use UnexpectedValueException;

use function unlink;

/**
 * Writes this package's contract types from the vendored artefact.
 *
 * Offline and deterministic: the same artefact in, the same files out, so CI
 * regenerates and fails on any difference.
 *
 * Each kind's schema is the whole envelope and carries the title `Envelope`,
 * which is the Rust type's name. The class is named from the kind instead, so
 * every kind is a class of its own rather than one more collision.
 *
 * Spec: 20-architecture/contracts/web-api.md
 */
final readonly class ContractGenerator
{
    /**
     * The `api_version` this package implements.
     */
    private const int SPOKEN_VERSION = 1;

    private const string ARTEFACT = 'contract/web-api.contract.json';

    private const string STAMP = 'contract/VERSION';

    private const string OUTPUT = 'src/Generated';

    /** The classes generated beside the envelopes, whose names no definition may take. */
    private const array CLASSES = ['Kind', 'RefusalCode', 'RefusalStatus', 'RefusalDescription', 'KeyCallableAction', 'Contract'];

    private const int MAX_DEPTH = 64;

    public function __construct(private string $root) {}

    public function run(): int
    {
        $artefact = $this->vendored();

        if ($artefact === null) {
            return 1;
        }

        $version = $this->spokenVersion($artefact);

        if ($version === null) {
            return 1;
        }

        $kinds = $this->generable($artefact);

        return $kinds === null ? 1 : $this->generate($artefact, $kinds, $version);
    }

    /**
     * Writes the kinds and the refusal codes, or nothing when the refusal codes cannot be read.
     *
     * @param  array<mixed, mixed>  $artefact
     * @param  array<mixed, mixed>  $kinds
     */
    private function generate(array $artefact, array $kinds, int $version): int
    {
        try {
            $refusals = new Refusals()->listed($artefact);
            $keyCallable = new KeyCallable()->listed($artefact);
        } catch (UnexpectedValueException $malformed) {
            return $this->refuse($malformed->getMessage() . ' Nothing was generated.');
        }

        return $this->emit($kinds, $refusals, $keyCallable, $version);
    }

    /**
     * The `api_version` the artefact names, or nothing when it names none this package generates from.
     *
     * @param  array<mixed, mixed>  $artefact
     */
    private function spokenVersion(array $artefact): ?int
    {
        $version = $artefact['api_version'] ?? null;

        if (! is_int($version)) {
            $this->refuse('The vendored contract names no whole-number api_version, so it is not a contract artefact.');

            return null;
        }

        if ($version !== self::SPOKEN_VERSION) {
            $this->refuse(sprintf(
                'The vendored contract is api_version %d and this package implements %d. Nothing was generated. Sync a revision this package speaks, or implement %d first.',
                $version,
                self::SPOKEN_VERSION,
                $version,
            ));

            return null;
        }

        return $version;
    }

    /**
     * The kinds the artefact describes, or nothing when it describes none that can be generated from.
     *
     * @param  array<mixed, mixed>  $artefact
     * @return array<mixed, mixed>|null
     */
    private function generable(array $artefact): ?array
    {
        $kinds = $artefact['kinds'] ?? null;

        if (! is_array($kinds)) {
            $this->refuse('The vendored contract names no kinds at all, so it is not a contract artefact.');

            return null;
        }

        if ($kinds === []) {
            $this->refuse('The vendored contract describes no kinds.');

            return null;
        }

        return $this->ordered($kinds);
    }

    /**
     * The same kinds in generation order, or nothing when one puts a constraint beside a reference.
     *
     * @param  array<mixed, mixed>  $kinds
     * @return array<mixed, mixed>|null
     */
    private function ordered(array $kinds): ?array
    {
        $references = new References();
        $ambiguous = $references->besideAReference($kinds, '');

        if ($ambiguous !== []) {
            $this->refuse(
                'The vendored contract puts a constraint beside a reference, and generating would drop one of the two: '
                . implode(', ', $ambiguous),
            );

            return null;
        }

        $dangling = $references->pointingAtNothing($kinds);

        if ($dangling !== []) {
            $this->refuse(sprintf(
                'The vendored contract holds references that resolve to no definition it carries, and every one of them '
                . 'would have been generated as mixed. In %s: %s. Nothing was generated.',
                self::ARTEFACT,
                implode(', ', $dangling),
            ));

            return null;
        }

        ksort($kinds);

        return $kinds;
    }

    /**
     * The vendored artefact, or nothing when it cannot be read.
     *
     * @return array<mixed, mixed>|null
     */
    private function vendored(): ?array
    {
        $text = $this->read();

        return $text === null ? null : $this->decoded($text);
    }

    /**
     * The vendored file's text, or nothing when there is none to read.
     */
    private function read(): ?string
    {
        $path = $this->root . '/' . self::ARTEFACT;

        if (! is_file($path)) {
            $this->refuse('There is no vendored contract at ' . self::ARTEFACT . '. Run `composer contract:sync -- <tag>` first.');

            return null;
        }

        $text = file_get_contents($path);

        if ($text === false) {
            $this->refuse(self::ARTEFACT . ' could not be read.');

            return null;
        }

        return $text;
    }

    /**
     * What the vendored text describes, or nothing when it is not an artefact.
     *
     * @return array<mixed, mixed>|null
     */
    private function decoded(string $text): ?array
    {
        try {
            $decoded = json_decode($text, true, self::MAX_DEPTH, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            $this->refuse(self::ARTEFACT . ' is not JSON: ' . $exception->getMessage());

            return null;
        }

        if (! is_array($decoded)) {
            $this->refuse(self::ARTEFACT . ' is not a contract artefact.');

            return null;
        }

        return $decoded;
    }

    /**
     * @param  array<mixed, mixed>  $kinds
     * @param  array<string, array{code: string, status: int, description: string}>  $refusals
     * @param  array<string, array{action: string, disturbs: bool, rehearsal: bool}>  $keyCallable
     */
    private function emit(array $kinds, array $refusals, array $keyCallable, int $version): int
    {
        $stamp = $this->stamp();

        try {
            $planned = $this->planned($kinds, $refusals, $keyCallable, $stamp, $version);
        } catch (UnexpectedValueException $unresolvable) {
            return $this->refuse(sprintf('In %s: %s Nothing was generated.', self::ARTEFACT, $unresolvable->getMessage()));
        }

        if ($planned === null) {
            return 1;
        }

        $over = LineCap::firstOver($planned['files']);

        return $over === null ? $this->write($planned, $stamp) : $this->refuse(sprintf(
            '%s would hold %d lines, over the %d a file may hold, so nothing was generated.',
            $over,
            LineCap::of($planned['files'][$over]),
            LineCap::MAX_LINES,
        ));
    }

    /**
     * Every file the kinds are written as and the kind each class name came
     * from, or nothing when a kind cannot be written at all.
     *
     * @param  array<mixed, mixed>  $kinds
     * @param  array<string, array{code: string, status: int, description: string}>  $refusals
     * @param  array<string, array{action: string, disturbs: bool, rehearsal: bool}>  $keyCallable
     * @return array{named: array<string, string>, refusals: int, files: array<string, string>}|null
     *
     * @throws UnexpectedValueException naming the reference, where a kind holds one that resolves to no definition
     */
    private function planned(array $kinds, array $refusals, array $keyCallable, string $stamp, int $version): ?array
    {
        $found = $this->classesOf($kinds);
        $plan = $found === null ? null : $this->planOf($found['classes']);

        if ($found === null || ! $plan instanceof ShapePlan) {
            return null;
        }

        ['named' => $named, 'classes' => $classes] = $found;
        $source = new GeneratedSource(self::ARTEFACT, $stamp, $version, $plan);
        $files = [self::OUTPUT . '/' . ShapePlan::SHARED . '.php' => $source->shapesClass()];

        foreach ($named as $name => $kind) {
            $files[self::OUTPUT . '/' . $name . 'Envelope.php'] = $source->envelopeClass($kind, $name, $classes[$name . 'Envelope']);
        }

        $files[self::OUTPUT . '/Kind.php'] = $source->kindEnum($named);
        $files[self::OUTPUT . '/RefusalCode.php'] = $source->refusalEnum($refusals);
        $files[self::OUTPUT . '/RefusalStatus.php'] = $source->refusalStatusClass($refusals);
        $files[self::OUTPUT . '/RefusalDescription.php'] = $source->refusalDescriptionClass($refusals);
        $files[self::OUTPUT . '/KeyCallableAction.php'] = $source->keyCallableEnum($keyCallable);
        $files[self::OUTPUT . '/Contract.php'] = $source->contractClass();

        return ['named' => $named, 'refusals' => count($refusals), 'files' => $files];
    }

    /**
     * Each kind's envelope class and the kind each class name came from, or
     * nothing when a kind cannot be written at all.
     *
     * @param  array<mixed, mixed>  $kinds
     * @return array{named: array<string, string>, classes: array<string, array<mixed, mixed>>}|null
     */
    private function classesOf(array $kinds): ?array
    {
        $named = [];
        $classes = [];

        foreach ($kinds as $kind => $schema) {
            if (! is_string($kind) || ! is_array($schema)) {
                $this->refuse('The vendored contract holds a kind that is not a named schema.');

                return null;
            }

            $name = $this->className($kind, $named);

            if ($name === null) {
                return null;
            }

            $named[$name] = $kind;
            $classes[$name . 'Envelope'] = $schema;
        }

        return ['named' => $named, 'classes' => $classes];
    }

    /**
     * Where each shape the envelope classes reach is named, or nothing when one cannot be named.
     *
     * @param  array<string, array<mixed, mixed>>  $classes
     */
    private function planOf(array $classes): ?ShapePlan
    {
        try {
            return new ShapePlan($classes, [...array_keys($classes), ...self::CLASSES]);
        } catch (UnexpectedValueException $unnamed) {
            $this->refuse($unnamed->getMessage() . ' Nothing was generated.');

            return null;
        }
    }

    /**
     * The class name a kind is written under, or nothing when it has none this generation can take.
     *
     * @param  array<string, string>  $named  class name to the kind it came from
     */
    private function className(string $kind, array $named): ?string
    {
        $name = $this->pascal($kind);

        if ($name === null) {
            $this->refuse(sprintf('The kind `%s` cannot be named as a PHP class.', $kind));

            return null;
        }

        if (array_key_exists($name, $named)) {
            $this->refuse(sprintf('The kinds `%s` and `%s` would both be named %s.', $named[$name], $kind, $name));

            return null;
        }

        return $name;
    }

    /**
     * @param  array{named: array<string, string>, refusals: int, files: array<string, string>}  $planned
     */
    private function write(array $planned, string $stamp): int
    {
        $directory = $this->root . '/' . self::OUTPUT;

        if (! is_dir($directory) && ! mkdir($directory, 0o755, true) && ! is_dir($directory)) {
            return $this->refuse(sprintf('%s could not be made, so nothing was generated.', self::OUTPUT));
        }

        $this->clear();

        foreach ($planned['files'] as $path => $contents) {
            if (file_put_contents($this->root . '/' . $path, $contents) === false) {
                return $this->refuse(sprintf('%s could not be written, so what is on disk is now incomplete.', $path));
            }
        }

        echo sprintf(
            "contract: %d kinds and %d refusal codes generated from %s into %s\n",
            count($planned['named']),
            $planned['refusals'],
            $stamp,
            self::OUTPUT,
        );

        return 0;
    }

    /**
     * The revision the vendored artefact was taken from.
     */
    private function stamp(): string
    {
        $text = file_get_contents($this->root . '/' . self::STAMP);

        return $text === false ? 'an unrecorded revision' : trim($text);
    }

    /**
     * Removes what an earlier generation wrote, so a kind that is gone leaves.
     */
    private function clear(): void
    {
        $found = glob($this->root . '/' . self::OUTPUT . '/*.php');

        foreach ($found === false ? [] : $found as $path) {
            unlink($path);
        }
    }

    /**
     * The class name a kind is written under, or nothing when it has none.
     */
    private function pascal(string $kind): ?string
    {
        $parts = preg_split('/[^A-Za-z0-9]+/', $kind);
        $name = '';

        foreach ($parts === false ? [] : $parts as $part) {
            $name .= ucfirst($part);
        }

        if (preg_match('/^[A-Za-z][A-Za-z0-9]*$/', $name) !== 1 || strtolower($name) === 'class') {
            return null;
        }

        return ucfirst($name);
    }

    private function refuse(string $message): int
    {
        fwrite(STDERR, 'contract: ' . $message . "\n");

        return 1;
    }
}

exit(new ContractGenerator(dirname(__DIR__))->run());
