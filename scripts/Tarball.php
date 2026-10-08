<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Scripts;

use function explode;
use function gzdecode;
use function in_array;
use function intdiv;
use function is_string;
use function octdec;
use function preg_match;
use function restore_error_handler;
use function rtrim;
use function set_error_handler;
use function sprintf;
use function str_ends_with;
use function str_repeat;
use function str_starts_with;
use function strlen;
use function strpos;
use function substr;
use function trim;

use UnexpectedValueException;

/**
 * The files a gzipped tar archive of a repository holds under one directory.
 *
 * An archive GitHub serves for a revision holds one top directory named after
 * the repository and the revision, and every file under it. Paths are read
 * from the ustar header, its prefix field, or a pax or GNU record naming a
 * longer one. Only regular files are read; a link or a device is not a file
 * the contract holds.
 */
final readonly class Tarball
{
    private const int BLOCK = 512;

    private const int NAME_LENGTH = 100;

    private const int SIZE_AT = 124;

    private const int SIZE_LENGTH = 12;

    private const int TYPE_AT = 156;

    private const int PREFIX_AT = 345;

    private const int PREFIX_LENGTH = 155;

    /**
     * The type flags a regular file is written with.
     *
     * @var list<string>
     */
    private const array REGULAR = ['0', "\0"];

    /**
     * A pax record naming the entry after it.
     */
    private const string PAX = 'x';

    /**
     * A GNU record naming the entry after it.
     */
    private const string GNU_LONG_NAME = 'L';

    /**
     * Every regular file under the directory, keyed by its path relative to it, in archive order.
     *
     * @param  string  $directory  relative to the archive's top directory, without a trailing slash
     * @return array<string, string>
     *
     * @throws UnexpectedValueException where the archive cannot be read, or holds a path that climbs out of where it is
     */
    public function filesUnder(string $gzipped, string $directory): array
    {
        $files = [];
        $named = null;

        foreach ($this->entries($this->inflated($gzipped)) as $entry) {
            if ($entry['type'] === self::PAX) {
                $named = $this->paxPath($entry['body']) ?? $named;

                continue;
            }

            if ($entry['type'] === self::GNU_LONG_NAME) {
                $named = rtrim($entry['body'], "\0");

                continue;
            }

            $path = $named ?? $entry['path'];
            $named = null;
            $relative = in_array($entry['type'], self::REGULAR, true) ? $this->under($path, $directory) : null;

            if ($relative !== null) {
                $files[$relative] = $entry['body'];
            }
        }

        return $files;
    }

    /**
     * @throws UnexpectedValueException
     */
    private function inflated(string $gzipped): string
    {
        set_error_handler(static fn(): bool => true);

        try {
            $archive = gzdecode($gzipped);
        } finally {
            restore_error_handler();
        }

        if (! is_string($archive)) {
            throw new UnexpectedValueException('What was fetched is not a gzipped archive.');
        }

        return $archive;
    }

    /**
     * Every entry the archive holds up to its end, with the type flag and the path its header gives.
     *
     * @return list<array{type: string, path: string, body: string}>
     *
     * @throws UnexpectedValueException
     */
    private function entries(string $archive): array
    {
        $entries = [];
        $at = 0;

        while (true) {
            $header = substr($archive, $at, self::BLOCK);

            if (strlen($header) < self::BLOCK) {
                throw new UnexpectedValueException('The archive ends partway through an entry.');
            }

            if ($header === str_repeat("\0", self::BLOCK)) {
                return $entries;
            }

            $size = $this->size($header);
            $body = substr($archive, $at + self::BLOCK, $size);

            if (strlen($body) < $size) {
                throw new UnexpectedValueException('The archive ends partway through an entry.');
            }

            $entries[] = ['type' => $header[self::TYPE_AT], 'path' => $this->headerPath($header), 'body' => $body];
            $at += self::BLOCK + intdiv($size + self::BLOCK - 1, self::BLOCK) * self::BLOCK;
        }
    }

    /**
     * Where a path sits relative to the directory, or nothing where it is not under it.
     *
     * @throws UnexpectedValueException
     */
    private function under(string $path, string $directory): ?string
    {
        $slash = strpos($path, '/');
        $inside = $slash === false ? '' : substr($path, $slash + 1);

        if (! str_starts_with($inside, $directory . '/')) {
            return null;
        }

        $relative = substr($inside, strlen($directory) + 1);

        foreach (explode('/', $relative) as $segment) {
            if (in_array($segment, ['', '.', '..'], true)) {
                throw new UnexpectedValueException(sprintf('The archive holds %s, which is no plain path under %s/.', $path, $directory));
            }
        }

        return $relative;
    }

    /**
     * @throws UnexpectedValueException
     */
    private function size(string $header): int
    {
        $field = trim(substr($header, self::SIZE_AT, self::SIZE_LENGTH), "\0 ");

        if (preg_match('/^[0-7]+$/', $field) !== 1) {
            throw new UnexpectedValueException('The archive holds an entry whose size is not written in octal.');
        }

        return (int) octdec($field);
    }

    private function headerPath(string $header): string
    {
        $name = rtrim(substr($header, 0, self::NAME_LENGTH), "\0");
        $prefix = rtrim(substr($header, self::PREFIX_AT, self::PREFIX_LENGTH), "\0");

        return $prefix === '' ? $name : $prefix . '/' . $name;
    }

    /**
     * The path a run of pax records names, or nothing where it names none.
     *
     * Each record is `<length> <key>=<value>` and a newline, its length counting all of it.
     *
     * @throws UnexpectedValueException
     */
    private function paxPath(string $records): ?string
    {
        $path = null;
        $at = 0;

        while ($at < strlen($records)) {
            $matches = [];

            if (preg_match('/\G(\d+) ([^=]*)=/', $records, $matches, 0, $at) !== 1) {
                throw new UnexpectedValueException('The archive holds a pax record it does not write as `<length> <key>=<value>`.');
            }

            $length = (int) $matches[1];
            $record = substr($records, $at, $length);

            if ($length <= strlen($matches[0]) || ! str_ends_with($record, "\n")) {
                throw new UnexpectedValueException('The archive holds a pax record whose length is not its own.');
            }

            if ($matches[2] === 'path') {
                $path = substr($record, strlen($matches[0]), -1);
            }

            $at += $length;
        }

        return $path;
    }
}
