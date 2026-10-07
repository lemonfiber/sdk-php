<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Scripts;

use function array_key_exists;
use function array_pop;
use function basename;
use function dirname;
use function explode;
use function file_get_contents;
use function glob;
use function implode;
use function is_array;
use function is_dir;
use function is_file;
use function is_string;
use function json_decode;

use JsonException;

use function ltrim;
use function preg_match;
use function sort;
use function sprintf;
use function str_contains;
use function str_starts_with;
use function strlen;
use function substr;

use UnexpectedValueException;

/**
 * The contract artefact vendored under `contract/`, read in whichever layout it is in.
 *
 * The directory `contract/web-api/` is an index naming one file per kind and
 * per list, with every definition in a file of its own under `defs/` and each
 * `$ref` a path resolved against the file it appears in. The single file
 * `contract/web-api.contract.json` holds the same content in one document,
 * each kind carrying its own `$defs`.
 *
 * Either is read into the single file's shape: each kind carries, as `$defs`,
 * every definition it reaches, and each reference names one of them as
 * `#/$defs/<Name>`, a definition's name being its file's name. The same
 * content in either layout is then the same artefact to whatever reads it.
 */
final readonly class VendoredContract
{
    public const string FILE = 'contract/web-api.contract.json';

    public const string DIRECTORY = 'contract/web-api';

    public const string INDEX = 'index.json';

    /**
     * Where the revision the copy came from is recorded.
     */
    public const string STAMP = 'contract/VERSION';

    /**
     * The lists the index names a file for, each read into the artefact under the same key.
     *
     * @var list<string>
     */
    public const array LISTS = ['key_callable', 'reads', 'refusals'];

    /**
     * How deeply a vendored file may nest.
     */
    public const int MAX_DEPTH = 64;

    /**
     * Where the definitions are, relative to the directory.
     */
    private const string DEFINITIONS = 'defs';

    private const string JSON = '.json';

    /**
     * What JSON allows ahead of a value.
     */
    private const string WHITESPACE = " \t\n\r";

    public function __construct(private string $root) {}

    /**
     * The vendored artefact in the single file's shape.
     *
     * @return array<mixed, mixed>
     *
     * @throws UnexpectedValueException naming what is wrong, where there is no artefact to read
     */
    public function artefact(): array
    {
        $file = is_file($this->root . '/' . self::FILE);
        $directory = is_dir($this->root . '/' . self::DIRECTORY);

        if ($file && $directory) {
            throw new UnexpectedValueException(sprintf(
                'Both %s and %s/ are vendored, so which is the contract is not settled. Run `composer contract:sync -- <revision>` again.',
                self::FILE,
                self::DIRECTORY,
            ));
        }

        if ($directory) {
            return $this->directory();
        }

        if (! $file) {
            throw new UnexpectedValueException(sprintf(
                'There is no vendored contract at %s/ or %s. Run `composer contract:sync -- <revision>` first.',
                self::DIRECTORY,
                self::FILE,
            ));
        }

        return $this->object(self::FILE);
    }

    /**
     * Where the vendored artefact is, as a refusal names it.
     */
    public function source(): string
    {
        return is_dir($this->root . '/' . self::DIRECTORY) ? self::DIRECTORY . '/' : self::FILE;
    }

    /**
     * @return array<mixed, mixed>
     *
     * @throws UnexpectedValueException
     */
    private function directory(): array
    {
        $index = $this->object(self::DIRECTORY . '/' . self::INDEX);
        $pool = $this->definitions();
        $unresolved = $pool['unresolved'];
        $artefact = $index;

        foreach (self::LISTS as $list) {
            if (array_key_exists($list, $index)) {
                $named = $this->named($index[$list], $list);
                $artefact[$list] = $this->decoded($this->text($named), $named);
            }
        }

        $kinds = $index['kinds'] ?? null;

        if (is_array($kinds)) {
            $artefact['kinds'] = [];

            foreach ($kinds as $kind => $path) {
                $named = $this->named($path, sprintf('the kind `%s`', $kind));
                $read = $this->resolved($this->object($named), $named);
                $unresolved = [...$unresolved, ...$read['unresolved']];
                $artefact['kinds'][$kind] = $this->carrying($read['schema'], $read['reached'], $pool['defs']);
            }
        }

        if ($unresolved !== []) {
            throw new UnexpectedValueException(sprintf(
                'The vendored contract holds references that resolve to no definition in the vendored copy, and every one of them '
                . 'would have been generated as mixed: %s. Nothing was generated.',
                implode(', ', $unresolved),
            ));
        }

        return $artefact;
    }

    /**
     * Every definition under `defs/`, keyed by name, each with its references resolved.
     *
     * Read whole rather than only as far as the kinds reach, so a reference that
     * resolves to nothing is refused wherever it is written.
     *
     * @return array{defs: array<string, array{schema: array<mixed, mixed>, reached: list<string>}>, unresolved: list<string>}
     *
     * @throws UnexpectedValueException
     */
    private function definitions(): array
    {
        $found = glob($this->root . '/' . self::DIRECTORY . '/' . self::DEFINITIONS . '/*' . self::JSON);
        $paths = $found === false ? [] : $found;
        sort($paths);

        $defs = [];
        $unresolved = [];

        foreach ($paths as $path) {
            $named = self::DIRECTORY . '/' . self::DEFINITIONS . '/' . basename($path);
            $schema = $this->object($named);
            unset($schema['$schema']);

            $read = $this->resolved($schema, $named);
            $defs[basename($path, self::JSON)] = ['schema' => $read['schema'], 'reached' => $read['reached']];
            $unresolved = [...$unresolved, ...$read['unresolved']];
        }

        return ['defs' => $defs, 'unresolved' => $unresolved];
    }

    /**
     * A kind's schema carrying every definition it reaches, however indirectly.
     *
     * @param  array<mixed, mixed>  $schema
     * @param  list<string>  $reached
     * @param  array<string, array{schema: array<mixed, mixed>, reached: list<string>}>  $defs
     * @return array<mixed, mixed>
     */
    private function carrying(array $schema, array $reached, array $defs): array
    {
        $carried = [];
        $pending = $reached;

        while ($pending !== []) {
            $name = array_pop($pending);

            if (array_key_exists($name, $carried) || ! array_key_exists($name, $defs)) {
                continue;
            }

            $carried[$name] = $defs[$name]['schema'];
            $pending = [...$pending, ...$defs[$name]['reached']];
        }

        if ($carried !== []) {
            $schema['$defs'] = $carried;
        }

        return $schema;
    }

    /**
     * A schema with every reference in it written as `#/$defs/<Name>`, the
     * names it reaches, and every reference that resolves to no definition.
     *
     * @param  array<mixed, mixed>  $node
     * @return array{schema: array<mixed, mixed>, reached: list<string>, unresolved: list<string>}
     */
    private function resolved(array $node, string $file): array
    {
        $reached = [];
        $unresolved = [];

        foreach ($node as $key => $value) {
            if ($key === '$ref') {
                $name = $this->definitionAt($value, $file);

                if ($name === null) {
                    $unresolved[] = sprintf('%s -> %s', $file, References::written($value));

                    continue;
                }

                $node[$key] = ShapePlan::DEFINITIONS . $name;
                $reached[] = $name;
            } elseif (is_array($value)) {
                $inner = $this->resolved($value, $file);
                $node[$key] = $inner['schema'];
                $reached = [...$reached, ...$inner['reached']];
                $unresolved = [...$unresolved, ...$inner['unresolved']];
            }
        }

        return ['schema' => $node, 'reached' => $reached, 'unresolved' => $unresolved];
    }

    /**
     * The name of the definition a reference resolves to from the file it is in, or nothing where it resolves to none.
     *
     * A reference is a relative path to a definition's file. One carrying a
     * fragment, a scheme, or a path that leaves the directory resolves to none.
     */
    private function definitionAt(mixed $reference, string $file): ?string
    {
        if (! is_string($reference) || str_contains($reference, '#') || str_contains($reference, ':')) {
            return null;
        }

        $path = $this->within(dirname($file) . '/' . $reference);
        $prefix = self::DIRECTORY . '/' . self::DEFINITIONS . '/';

        if ($path === null || ! str_starts_with($path, $prefix) || preg_match('~^[^/]+\.json$~', substr($path, strlen($prefix))) !== 1) {
            return null;
        }

        return is_file($this->root . '/' . $path) ? basename($path, self::JSON) : null;
    }

    /**
     * The file a path the index gives names, relative to the root.
     *
     * @throws UnexpectedValueException where it names none inside the directory
     */
    private function named(mixed $path, string $what): string
    {
        $resolved = is_string($path) && ! str_contains($path, ':') ? $this->within(self::DIRECTORY . '/' . $path) : null;

        if ($resolved === null || ! str_starts_with($resolved, self::DIRECTORY . '/')) {
            throw new UnexpectedValueException(sprintf(
                '%s/%s names %s as %s, which is no file inside %s/.',
                self::DIRECTORY,
                self::INDEX,
                References::written($path),
                $what,
                self::DIRECTORY,
            ));
        }

        return $resolved;
    }

    /**
     * A path with every `.` and `..` taken out, or nothing where it climbs out of the root or names nothing.
     */
    private function within(string $path): ?string
    {
        $kept = [];

        foreach (explode('/', $path) as $segment) {
            if ($segment === '' || $segment === '.') {
                continue;
            }

            if ($segment !== '..') {
                $kept[] = $segment;

                continue;
            }

            if ($kept === []) {
                return null;
            }

            array_pop($kept);
        }

        return $kept === [] ? null : implode('/', $kept);
    }

    /**
     * What a vendored file holds, which must be a JSON object.
     *
     * @return array<mixed, mixed>
     *
     * @throws UnexpectedValueException
     */
    private function object(string $named): array
    {
        $text = $this->text($named);
        $decoded = $this->decoded($text, $named);

        if (! is_array($decoded) || ! str_starts_with(ltrim($text, self::WHITESPACE), '{')) {
            throw new UnexpectedValueException(sprintf('%s is not a JSON object, so it is not a contract artefact.', $named));
        }

        return $decoded;
    }

    /**
     * @throws UnexpectedValueException
     */
    private function text(string $named): string
    {
        $path = $this->root . '/' . $named;
        $text = is_file($path) ? file_get_contents($path) : false;

        if ($text === false) {
            throw new UnexpectedValueException(sprintf('%s could not be read.', $named));
        }

        return $text;
    }

    /**
     * What a vendored file's text holds.
     *
     * @throws UnexpectedValueException
     */
    private function decoded(string $text, string $named): mixed
    {
        try {
            return json_decode($text, true, self::MAX_DEPTH, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new UnexpectedValueException(sprintf('%s is not JSON: %s', $named, $exception->getMessage()), 0, $exception);
        }
    }
}
