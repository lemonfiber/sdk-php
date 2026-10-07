<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Tests\Support;

use function array_sum;
use function gzencode;
use function ord;
use function sprintf;
use function str_pad;
use function str_repeat;
use function strlen;
use function substr;

/**
 * A gzipped tar archive shaped as the one GitHub serves for a revision.
 *
 * One top directory named after the repository and the revision, a pax global
 * header ahead of it carrying the commit, and every file under the top
 * directory. A path longer than the ustar name field travels in a pax record.
 */
final class Archive
{
    public const string TOP = 'lemonfiber-lemonfiber-69260f0';

    private const int BLOCK = 512;

    private const int NAME_LENGTH = 100;

    /**
     * @param  array<string, string>  $files  path under the top directory to contents
     * @param  array<string, string>  $links  path under the top directory to what the link points at
     */
    public static function of(array $files, array $links = []): string
    {
        $archive = self::entry('pax_global_header', 'g', "52 comment=69260f08cd603adc348d1c01eb398ae6b02cd8da\n")
            . self::entry(self::TOP . '/', '5', '');

        foreach ($files as $path => $contents) {
            $archive .= self::file(self::TOP . '/' . $path, $contents);
        }

        foreach ($links as $path => $target) {
            $archive .= self::entry(self::TOP . '/' . $path, '2', '', $target);
        }

        return (string) gzencode($archive . str_repeat("\0", self::BLOCK * 2));
    }

    private static function file(string $path, string $contents): string
    {
        if (strlen($path) < self::NAME_LENGTH) {
            return self::entry($path, '0', $contents);
        }

        $record = ' path=' . $path . "\n";
        $length = strlen($record);
        $length += strlen((string) ($length + strlen((string) $length)));

        return self::entry('PaxHeaders/' . substr($path, -80), 'x', $length . $record)
            . self::entry(substr($path, 0, self::NAME_LENGTH - 1), '0', $contents);
    }

    private static function entry(string $name, string $type, string $body, string $link = ''): string
    {
        $header = str_pad($name, self::NAME_LENGTH, "\0")
            . sprintf('%07o', 0o644) . "\0"
            . sprintf('%07o', 0) . "\0"
            . sprintf('%07o', 0) . "\0"
            . sprintf('%011o', strlen($body)) . "\0"
            . sprintf('%011o', 0) . "\0"
            . str_repeat(' ', 8)
            . $type
            . str_pad($link, self::NAME_LENGTH, "\0")
            . "ustar\0" . '00'
            . str_repeat("\0", 32 * 2 + 8 * 2 + 155);
        $header = str_pad($header, self::BLOCK, "\0");
        $checksum = array_sum(self::weighed($header));
        $header = substr($header, 0, 148) . sprintf('%06o', $checksum) . "\0 " . substr($header, 156);
        $padding = (self::BLOCK - strlen($body) % self::BLOCK) % self::BLOCK;

        return $header . $body . str_repeat("\0", $padding);
    }

    /**
     * Every byte's value, for the header's checksum.
     *
     * @return list<int>
     */
    private static function weighed(string $header): array
    {
        $values = [];

        for ($at = 0, $length = strlen($header); $at < $length; ++$at) {
            $values[] = ord($header[$at]);
        }

        return $values;
    }
}
