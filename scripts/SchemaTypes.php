<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Scripts;

use function array_key_exists;
use function array_map;
use function array_unique;
use function array_values;
use function count;
use function implode;
use function in_array;
use function is_array;
use function is_bool;
use function is_float;
use function is_int;
use function is_string;
use function json_encode;
use function preg_match;
use function sprintf;
use function str_replace;
use function str_starts_with;
use function strlen;
use function substr;

use UnexpectedValueException;

/**
 * A JSON Schema written as the PHPStan type it describes.
 *
 * The contract artefact is JSON Schema, and PHP carries a decoded payload as an
 * array. An array shape is what says which keys that array holds and what each
 * one is, so a shape is what a schema becomes here. Anything the schema leaves
 * open becomes `mixed`. A reference to a definition already being expanded is
 * one of those: an array shape cannot hold itself.
 */
final class SchemaTypes
{
    /**
     * The type given to anything the schema does not describe.
     */
    public const string UNKNOWN = 'mixed';

    /**
     * What every reference this resolves opens with, before the definition's name.
     */
    public const string DEFINITIONS = '#/$defs/';

    /**
     * The PHPStan type a schema describes.
     *
     * @param  array<mixed, mixed>  $schema
     * @param  array<mixed, mixed>  $defs
     * @param  list<string>  $seen  the definitions already being expanded
     *
     * @throws UnexpectedValueException naming the reference, where one resolves to no definition in `$defs`
     */
    public function typeOf(array $schema, array $defs, array $seen = []): string
    {
        $enum = $schema['enum'] ?? null;
        $combined = $this->combined($schema);

        return match (true) {
            array_key_exists('$ref', $schema) => $this->typeOfReference($schema['$ref'], $defs, $seen),
            array_key_exists('const', $schema) => $this->literal($schema['const']),
            is_array($enum) && $enum !== [] => $this->union(array_map($this->literal(...), array_values($enum))),
            $combined !== null => $this->union($this->typesOf($combined, $defs, $seen)),
            default => $this->typeOfNamed($schema['type'] ?? null, $schema, $defs, $seen),
        };
    }

    /**
     * The name of the definition in `$defs` a reference points at, or nothing where it points at none.
     *
     * @param  array<mixed, mixed>  $defs
     */
    public function definitionNamed(mixed $reference, array $defs): ?string
    {
        if (! is_string($reference) || ! str_starts_with($reference, self::DEFINITIONS)) {
            return null;
        }

        $name = substr($reference, strlen(self::DEFINITIONS));

        return is_array($defs[$name] ?? null) ? $name : null;
    }

    /**
     * A reference as the contract writes it, for a refusal to name.
     */
    public function written(mixed $reference): string
    {
        return json_encode($reference, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }

    public function quoted(string $value): string
    {
        return "'" . str_replace(['\\', "'"], ['\\\\', "\\'"], $value) . "'";
    }

    /**
     * The sub-schemas a combining keyword holds, or nothing where a schema combines none.
     *
     * @param  array<mixed, mixed>  $schema
     * @return array<mixed, mixed>|null
     */
    private function combined(array $schema): ?array
    {
        $branches = $schema['oneOf'] ?? $schema['anyOf'] ?? null;

        if (is_array($branches) && $branches !== []) {
            return $branches;
        }

        $all = $schema['allOf'] ?? null;

        return is_array($all) && count($all) === 1 ? $all : null;
    }

    /**
     * @param  array<mixed, mixed>  $schema
     * @param  array<mixed, mixed>  $defs
     * @param  list<string>  $seen
     */
    private function typeOfNamed(mixed $named, array $schema, array $defs, array $seen): string
    {
        if (is_string($named)) {
            return match ($named) {
                'string' => 'string',
                'integer' => 'int',
                'number' => 'float',
                'boolean' => 'bool',
                'null' => 'null',
                'array' => $this->listType($schema, $defs, $seen),
                'object' => $this->objectType($schema, $defs, $seen),
                default => self::UNKNOWN,
            };
        }

        if (is_array($named) && $named !== []) {
            $types = [];

            foreach ($named as $one) {
                $types[] = $this->typeOfNamed(is_string($one) ? $one : null, $schema, $defs, $seen);
            }

            return $this->union($types);
        }

        return self::UNKNOWN;
    }

    /**
     * @param  array<mixed, mixed>  $defs
     * @param  list<string>  $seen
     *
     * @throws UnexpectedValueException
     */
    private function typeOfReference(mixed $reference, array $defs, array $seen): string
    {
        $name = $this->definitionNamed($reference, $defs);
        $target = $name === null ? null : $defs[$name];

        if ($name === null || ! is_array($target)) {
            throw new UnexpectedValueException(sprintf(
                'The reference %s resolves to no definition the schema carries.',
                $this->written($reference),
            ));
        }

        if (in_array($name, $seen, true)) {
            return self::UNKNOWN;
        }

        return $this->typeOf($target, $defs, [...$seen, $name]);
    }

    /**
     * @param  array<mixed, mixed>  $schema
     * @param  array<mixed, mixed>  $defs
     * @param  list<string>  $seen
     */
    private function listType(array $schema, array $defs, array $seen): string
    {
        $fixed = $schema['prefixItems'] ?? null;

        if (is_array($fixed) && $fixed !== []) {
            return 'array{' . implode(', ', $this->typesOf($fixed, $defs, $seen)) . '}';
        }

        $items = $schema['items'] ?? null;

        return 'list<' . (is_array($items) ? $this->typeOf($items, $defs, $seen) : self::UNKNOWN) . '>';
    }

    /**
     * @param  array<mixed, mixed>  $schema
     * @param  array<mixed, mixed>  $defs
     * @param  list<string>  $seen
     */
    private function objectType(array $schema, array $defs, array $seen): string
    {
        $properties = $schema['properties'] ?? null;

        if (is_array($properties) && $properties !== []) {
            return $this->shape($properties, $this->requiredIn($schema), $defs, $seen);
        }

        $additional = $schema['additionalProperties'] ?? null;

        if (is_array($additional)) {
            return 'array<string, ' . $this->typeOf($additional, $defs, $seen) . '>';
        }

        return $additional === false ? 'array{}' : 'array<string, ' . self::UNKNOWN . '>';
    }

    /**
     * @param  array<mixed, mixed>  $properties
     * @param  list<string>  $required
     * @param  array<mixed, mixed>  $defs
     * @param  list<string>  $seen
     */
    private function shape(array $properties, array $required, array $defs, array $seen): string
    {
        $fields = [];

        foreach ($properties as $name => $property) {
            if (! is_string($name)) {
                continue;
            }

            $fields[] = $this->key($name)
                . (in_array($name, $required, true) ? '' : '?')
                . ': '
                . (is_array($property) ? $this->typeOf($property, $defs, $seen) : self::UNKNOWN);
        }

        return $fields === [] ? 'array{}' : 'array{' . implode(', ', $fields) . '}';
    }

    /**
     * @param  array<mixed, mixed>  $schema
     * @return list<string>
     */
    private function requiredIn(array $schema): array
    {
        $required = $schema['required'] ?? null;
        $names = [];

        foreach (is_array($required) ? $required : [] as $name) {
            if (is_string($name)) {
                $names[] = $name;
            }
        }

        return $names;
    }

    /**
     * @param  array<mixed, mixed>  $schemas
     * @param  array<mixed, mixed>  $defs
     * @param  list<string>  $seen
     * @return list<string>
     */
    private function typesOf(array $schemas, array $defs, array $seen): array
    {
        $types = [];

        foreach ($schemas as $schema) {
            $types[] = is_array($schema) ? $this->typeOf($schema, $defs, $seen) : self::UNKNOWN;
        }

        return $types;
    }

    /**
     * @param  list<string>  $types
     */
    private function union(array $types): string
    {
        $distinct = array_values(array_unique($types));

        if ($distinct === [] || in_array(self::UNKNOWN, $distinct, true)) {
            return self::UNKNOWN;
        }

        return implode('|', $distinct);
    }

    private function literal(mixed $value): string
    {
        return match (true) {
            is_string($value) => $this->quoted($value),
            is_bool($value) => $value ? 'true' : 'false',
            is_int($value) => (string) $value,
            is_float($value) => 'float',
            $value === null => 'null',
            default => self::UNKNOWN,
        };
    }

    private function key(string $name): string
    {
        return preg_match('/^[A-Za-z_]\w*$/', $name) === 1 ? $name : $this->quoted($name);
    }
}
