<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Tests\Support;

use function dirname;
use function in_array;
use function is_array;
use function is_string;

use Lemonfiber\Sdk\Scripts\VendoredContract;
use LogicException;

/**
 * One definition a kind of the vendored contract carries, read as the contract writes it.
 */
final class VendoredDefinition
{
    /**
     * The tag each branch of the definition's union carries in its `of`, in the order the contract lists them.
     *
     * @return list<string>
     */
    public static function tags(string $kind, string $definition): array
    {
        $branches = self::of($kind, $definition)['oneOf'] ?? null;
        $tags = [];

        foreach (is_array($branches) ? $branches : [] as $branch) {
            $properties = is_array($branch) ? $branch['properties'] ?? null : null;
            $of = is_array($properties) && is_array($properties['of'] ?? null) ? $properties['of']['const'] ?? null : null;

            if (! is_string($of)) {
                throw new LogicException($kind . ' gives ' . $definition . ' a branch tagged by no `of`');
            }

            $tags[] = $of;
        }

        return $tags;
    }

    /**
     * What the definition says of one of its properties.
     *
     * @return array<mixed, mixed>
     */
    public static function property(string $kind, string $definition, string $property): array
    {
        $properties = self::of($kind, $definition)['properties'] ?? null;
        $declared = is_array($properties) ? $properties[$property] ?? null : null;

        if (! is_array($declared)) {
            throw new LogicException($kind . ' gives ' . $definition . ' no ' . $property);
        }

        return $declared;
    }

    /**
     * Whether the definition requires the property.
     */
    public static function requires(string $kind, string $definition, string $property): bool
    {
        $required = self::of($kind, $definition)['required'] ?? null;

        return is_array($required) && in_array($property, $required, true);
    }

    /**
     * @return array<mixed, mixed>
     */
    private static function of(string $kind, string $definition): array
    {
        $kinds = new VendoredContract(dirname(__DIR__, 2))->artefact()['kinds'] ?? null;
        $schema = is_array($kinds) ? $kinds[$kind] ?? null : null;
        $definitions = is_array($schema) ? $schema['$defs'] ?? null : null;
        $found = is_array($definitions) ? $definitions[$definition] ?? null : null;

        if (! is_array($found)) {
            throw new LogicException('The vendored ' . $kind . ' kind defines no ' . $definition);
        }

        return $found;
    }
}
