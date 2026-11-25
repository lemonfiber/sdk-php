<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Scripts;

use function array_diff;
use function array_key_exists;
use function array_keys;
use function array_map;
use function array_unique;
use function array_values;
use function implode;
use function in_array;
use function is_array;
use function is_bool;
use function is_float;
use function is_int;
use function is_string;
use function preg_match;
use function sprintf;
use function str_replace;
use function strval;

use UnexpectedValueException;

/**
 * A JSON Schema written as the PHPStan type it describes.
 *
 * The contract artefact is JSON Schema, and PHP carries a decoded payload as an
 * array. An array shape is what says which keys that array holds and what each
 * one is, so a shape is what a schema becomes here. Anything the schema leaves
 * open becomes `mixed`. A reference to a definition the plan names is that
 * definition's alias, and a reference to anything else is refused.
 */
final readonly class SchemaTypes
{
    /**
     * The type given to anything the schema does not describe.
     */
    public const string UNKNOWN = 'mixed';

    /** Every PHPStan keyword a type this writes may spell. */
    public const array KEYWORDS = [self::UNKNOWN, 'list', 'array', 'int', 'float', 'string', 'bool', 'null', 'true', 'false'];

    /** Every JSON Schema keyword this reads. Any other is refused rather than dropped. */
    public const array READ = [
        '$comment', '$defs', '$ref', '$schema', 'additionalProperties', 'anyOf', 'const', 'default', 'description',
        'enum', 'examples', 'exclusiveMaximum', 'exclusiveMinimum', 'format', 'items', 'maxItems', 'maxLength',
        'maximum', 'minItems', 'minLength', 'minimum', 'oneOf', 'pattern', 'prefixItems', 'properties', 'required',
        'title', 'type', 'uniqueItems',
    ];

    /**
     * @param  ShapePlan  $plan  where each definition the payloads reach is named
     */
    public function __construct(private ShapePlan $plan) {}

    /**
     * The PHPStan type a schema describes.
     *
     * @param  array<mixed, mixed>  $schema
     * @param  string|null  $within  the definition whose alias this schema is part of, if any
     *
     * @throws UnexpectedValueException naming the reference, where one resolves to no definition the plan names,
     *                                   and naming the keyword, where the schema uses one this does not read
     */
    public function typeOf(array $schema, ?string $within = null): string
    {
        $unread = array_diff(array_map(strval(...), array_keys($schema)), self::READ);

        if ($unread !== []) {
            throw new UnexpectedValueException(sprintf(
                'A schema in %s uses %s, which this generator does not read.',
                $within ?? 'a payload',
                implode(', ', $unread),
            ));
        }

        $enum = $schema['enum'] ?? null;
        $combined = $this->combined($schema);

        return match (true) {
            array_key_exists('$ref', $schema) => $this->typeOfReference($schema['$ref'], $within),
            array_key_exists('const', $schema) => $this->literal($schema['const']),
            is_array($enum) && $enum !== [] => $this->union(array_map($this->literal(...), array_values($enum))),
            $combined !== null => $this->union($this->typesOf($this->beside($combined, $schema, $within), $within)),
            default => $this->typeOfNamed($schema['type'] ?? null, $schema, $within),
        };
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

        return is_array($branches) && $branches !== [] ? $branches : null;
    }

    /**
     * Each branch with the fields the schema holds beside the branches, as one object.
     *
     * @param  array<mixed, mixed>  $branches
     * @param  array<mixed, mixed>  $schema
     * @return array<mixed, mixed>
     *
     * @throws UnexpectedValueException where a branch is not an object those fields can join
     */
    private function beside(array $branches, array $schema, ?string $within): array
    {
        $properties = $schema['properties'] ?? null;

        if (! is_array($properties) || $properties === []) {
            return $branches;
        }

        $joined = [];

        foreach ($branches as $branch) {
            $own = is_array($branch) && ! array_key_exists('$ref', $branch) ? ($branch['properties'] ?? []) : null;

            if (! is_array($branch) || ! is_array($own)) {
                throw new UnexpectedValueException(sprintf('A schema in %s holds fields beside a branch they cannot join.', $within ?? 'a payload'));
            }

            $joined[] = [...$branch, 'type' => 'object', 'properties' => [...$properties, ...$own], 'required' => [...$this->requiredIn($schema), ...$this->requiredIn($branch)]];
        }

        return $joined;
    }

    /**
     * @param  array<mixed, mixed>  $schema
     */
    private function typeOfNamed(mixed $named, array $schema, ?string $within): string
    {
        if (is_string($named)) {
            return match ($named) {
                'string' => 'string',
                'integer' => 'int',
                'number' => 'float',
                'boolean' => 'bool',
                'null' => 'null',
                'array' => $this->listType($schema, $within),
                'object' => $this->objectType($schema, $within),
                default => self::UNKNOWN,
            };
        }

        if (is_array($named) && $named !== []) {
            $types = [];

            foreach ($named as $one) {
                $types[] = $this->typeOfNamed(is_string($one) ? $one : null, $schema, $within);
            }

            return $this->union($types);
        }

        return self::UNKNOWN;
    }

    /**
     * The alias a reference names, or `mixed` where it closes a cycle.
     *
     * @throws UnexpectedValueException naming the reference, where it resolves to no definition the plan names
     */
    private function typeOfReference(mixed $reference, ?string $within): string
    {
        $name = ShapePlan::definitionIn($reference);

        if ($name === null || ! $this->plan->names($name)) {
            throw new UnexpectedValueException(sprintf(
                'The reference %s resolves to no definition the schema carries.',
                References::written($reference),
            ));
        }

        return $this->plan->closesACycle($within, $name) ? self::UNKNOWN : $name;
    }

    /**
     * @param  array<mixed, mixed>  $schema
     */
    private function listType(array $schema, ?string $within): string
    {
        $fixed = $schema['prefixItems'] ?? null;

        if (is_array($fixed) && $fixed !== []) {
            return 'array{' . implode(', ', $this->typesOf($fixed, $within)) . '}';
        }

        $items = $schema['items'] ?? null;

        return 'list<' . (is_array($items) ? $this->typeOf($items, $within) : self::UNKNOWN) . '>';
    }

    /**
     * @param  array<mixed, mixed>  $schema
     */
    private function objectType(array $schema, ?string $within): string
    {
        $properties = $schema['properties'] ?? null;

        if (is_array($properties) && $properties !== []) {
            return $this->shape($properties, $this->requiredIn($schema), $within);
        }

        $additional = $schema['additionalProperties'] ?? null;

        if (is_array($additional)) {
            return 'array<string, ' . $this->typeOf($additional, $within) . '>';
        }

        return $additional === false ? 'array{}' : 'array<string, ' . self::UNKNOWN . '>';
    }

    /**
     * @param  array<mixed, mixed>  $properties
     * @param  list<string>  $required
     */
    private function shape(array $properties, array $required, ?string $within): string
    {
        $fields = [];

        foreach ($properties as $name => $property) {
            if (! is_string($name)) {
                continue;
            }

            $fields[] = $this->key($name)
                . (in_array($name, $required, true) ? '' : '?')
                . ': '
                . (is_array($property) ? $this->typeOf($property, $within) : self::UNKNOWN);
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
     * @return list<string>
     */
    private function typesOf(array $schemas, ?string $within): array
    {
        $types = [];

        foreach ($schemas as $schema) {
            $types[] = is_array($schema) ? $this->typeOf($schema, $within) : self::UNKNOWN;
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
