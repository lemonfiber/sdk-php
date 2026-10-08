<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Scripts;

use function array_all;
use function array_diff;
use function array_filter;
use function array_is_list;
use function array_key_exists;
use function array_keys;
use function array_map;
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
use function sprintf;
use function str_replace;
use function strval;
use function trim;

use UnexpectedValueException;

/**
 * One argument an action takes, read from the JSON Schema the contract gives it.
 *
 * Read into what a PHP constructor parameter needs: the type PHP checks, the
 * type static analysis reads, and the default. An argument the
 * contract gives no default is a parameter a caller must pass. A schema this
 * cannot write as a parameter, or a default that is not of the argument's own
 * type, is refused naming the action and the argument, rather than written as
 * a parameter that accepts something else.
 *
 * @phpstan-type Argument array{wire: string, param: string, native: string, doc: string, optional: bool, default: mixed, description: string}
 */
final readonly class ActionArgument
{
    /** The keywords an argument's schema may hold; any other is a constraint this cannot write. */
    private const array UNDERSTOOD = ['type', 'items', 'default', 'description', 'title', 'format', 'minimum', 'maximum'];

    /** Each JSON type this writes, to the PHP type it is written as. */
    private const array SCALARS = ['string' => 'string', 'boolean' => 'bool', 'integer' => 'int', 'number' => 'float'];

    private const string ARRAY = 'array';

    private const string NULL = 'null';

    public function __construct(
        private string $action,
        private string $wire,
        private string $param,
    ) {}

    /**
     * @param  array<mixed, mixed>  $schema
     * @return Argument
     *
     * @throws UnexpectedValueException naming the action and the argument
     */
    public function read(array $schema): array
    {
        $beyond = array_diff(array_keys($schema), self::UNDERSTOOD);

        if ($beyond !== []) {
            throw $this->refusal(sprintf('with `%s`, which a parameter cannot hold', implode('`, `', array_map(strval(...), $beyond))));
        }

        ['base' => $base, 'nullable' => $nullable] = $this->named($schema['type'] ?? null);
        $doc = $this->docType($base, $schema);

        return [
            'wire' => $this->wire,
            'param' => $this->param,
            'native' => ($nullable ? '?' : '') . ($base === self::ARRAY ? 'array' : self::SCALARS[$base]),
            'doc' => $nullable ? $doc . '|null' : $doc,
            'optional' => array_key_exists('default', $schema),
            'default' => array_key_exists('default', $schema) ? $this->defaultOf($schema['default'], $base, $nullable, $schema) : null,
            'description' => $this->described($schema['description'] ?? null),
        ];
    }

    /**
     * The one JSON type an argument is, and whether it may also be null.
     *
     * @return array{base: string, nullable: bool}
     *
     * @throws UnexpectedValueException
     */
    private function named(mixed $type): array
    {
        $types = is_string($type) ? [$type] : $type;

        if (! is_array($types) || ! array_is_list($types)) {
            throw $this->refusal('with no type');
        }

        $nullable = in_array(self::NULL, $types, true);
        $rest = array_values(array_filter($types, static fn(mixed $one): bool => $one !== self::NULL));

        if (count($rest) !== 1 || ! is_string($rest[0]) || ($rest[0] !== self::ARRAY && ! array_key_exists($rest[0], self::SCALARS))) {
            throw $this->refusal(sprintf('as %s, which is not one type a parameter can be', json_encode($type, JSON_UNESCAPED_SLASHES)));
        }

        return ['base' => $rest[0], 'nullable' => $nullable];
    }

    /**
     * The type static analysis reads: a list's item type, and an integer's range.
     *
     * @param  array<mixed, mixed>  $schema
     *
     * @throws UnexpectedValueException
     */
    private function docType(string $base, array $schema): string
    {
        if ($base === self::ARRAY) {
            return sprintf('list<%s>', self::SCALARS[$this->itemType($schema['items'] ?? null)]);
        }

        if ($base !== 'integer' || (! array_key_exists('minimum', $schema) && ! array_key_exists('maximum', $schema))) {
            return self::SCALARS[$base];
        }

        $minimum = $schema['minimum'] ?? null;
        $maximum = $schema['maximum'] ?? null;

        if (($minimum !== null && ! is_int($minimum)) || ($maximum !== null && ! is_int($maximum))) {
            throw $this->refusal('with a bound that is not a whole number');
        }

        return sprintf('int<%s, %s>', $minimum ?? 'min', $maximum ?? 'max');
    }

    /**
     * @throws UnexpectedValueException
     */
    private function itemType(mixed $items): string
    {
        $type = is_array($items) && array_keys($items) === ['type'] ? $items['type'] : null;

        if (! is_string($type) || ! array_key_exists($type, self::SCALARS)) {
            throw $this->refusal('as a list of something other than one plain type');
        }

        return $type;
    }

    /**
     * The default, where it is of the argument's own type.
     *
     * @param  array<mixed, mixed>  $schema
     *
     * @throws UnexpectedValueException
     */
    private function defaultOf(mixed $default, string $base, bool $nullable, array $schema): mixed
    {
        $fits = match (true) {
            $default === null => $nullable,
            $base === self::ARRAY => is_array($default) && array_is_list($default) && $this->allOf($default, $this->itemType($schema['items'] ?? null)),
            default => $this->isOf($default, $base),
        };

        if (! $fits) {
            throw $this->refusal(sprintf('the default %s, which is not of its type', json_encode($default, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)));
        }

        return $default;
    }

    /**
     * @param  list<mixed>  $values
     */
    private function allOf(array $values, string $type): bool
    {
        return array_all($values, fn(mixed $value): bool => $this->isOf($value, $type));
    }

    private function isOf(mixed $value, string $type): bool
    {
        return match ($type) {
            'string' => is_string($value),
            'boolean' => is_bool($value),
            'integer' => is_int($value),
            default => is_int($value) || is_float($value),
        };
    }

    /**
     * The description, as one line a docblock can carry.
     */
    private function described(mixed $description): string
    {
        if (! is_string($description)) {
            return '';
        }

        return trim(str_replace(['*/', "\r\n", "\n", "\r"], ['* /', ' ', ' ', ' '], $description));
    }

    private function refusal(string $what): UnexpectedValueException
    {
        return new UnexpectedValueException(sprintf('The action `%s` gives its argument `%s` %s.', $this->action, $this->wire, $what));
    }
}
