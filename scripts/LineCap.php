<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Scripts;

use function substr_count;

/**
 * The most lines a file may hold, generated files included, and how they are counted.
 *
 * The guards hold every file to it, and the generator refuses to write a file
 * that would hold more.
 */
final class LineCap
{
    public const int MAX_LINES = 550;

    /**
     * How many lines a file's source holds.
     */
    public static function of(string $source): int
    {
        return substr_count($source, "\n") + 1;
    }

    /**
     * The first of the files, by path, that holds more lines than the cap, or none where every one fits.
     *
     * @param  array<string, string>  $files  path to source
     */
    public static function firstOver(array $files): ?string
    {
        foreach ($files as $path => $source) {
            if (self::of($source) > self::MAX_LINES) {
                return $path;
            }
        }

        return null;
    }
}
