<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Tests\Support;

use function array_keys;
use function array_merge;
use function array_unique;
use function array_values;
use function file_get_contents;
use function in_array;
use function is_array;
use function is_string;
use function json_decode;
use function lcfirst;

use LogicException;
use ReflectionClass;
use ReflectionProperty;

use function sort;
use function str_replace;
use function ucwords;

/**
 * The fields a contract directory gives the envelope, beside `data`, and which of them a class carries.
 */
final class EnvelopeFields
{
    /**
     * Every top-level property any kind's schema under the directory declares, sorted.
     *
     * @return list<string>
     */
    public static function declaredUnder(string $directory): array
    {
        $kinds = self::decoded($directory . '/index.json')['kinds'] ?? null;

        if (! is_array($kinds)) {
            throw new LogicException($directory . '/index.json names no kinds');
        }

        $fields = [];

        foreach ($kinds as $file) {
            if (! is_string($file)) {
                throw new LogicException($directory . '/index.json names a kind by something other than a path');
            }

            $properties = self::decoded($directory . '/' . $file)['properties'] ?? null;

            if (! is_array($properties)) {
                throw new LogicException($directory . '/' . $file . ' declares no properties');
            }

            $fields = array_merge($fields, array_keys($properties));
        }

        $unique = array_values(array_unique($fields));
        sort($unique);

        return $unique;
    }

    /**
     * Each field, as the contract names it, that the class has no public property for.
     *
     * @param  class-string  $class
     * @param  list<string>  $fields
     * @return list<string>
     */
    public static function missingFrom(string $class, array $fields): array
    {
        $carried = [];

        foreach (new ReflectionClass($class)->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            $carried[] = $property->getName();
        }

        $missing = [];

        foreach ($fields as $field) {
            if (! in_array(self::asProperty($field), $carried, true)) {
                $missing[] = $field;
            }
        }

        return $missing;
    }

    /**
     * A contract's snake-case field name as the property it is carried in.
     */
    public static function asProperty(string $field): string
    {
        return lcfirst(str_replace('_', '', ucwords($field, '_')));
    }

    /**
     * @return array<array-key, mixed>
     */
    private static function decoded(string $path): array
    {
        $text = file_get_contents($path);
        $decoded = is_string($text) ? json_decode($text, true) : null;

        if (! is_array($decoded)) {
            throw new LogicException($path . ' is not a JSON object');
        }

        return $decoded;
    }
}
