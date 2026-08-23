<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Http;

use function fclose;
use function feof;
use function fread;

use Generator;
use Lemonfiber\Sdk\Time\Duration;

use function stream_set_timeout;

/**
 * Reads a stream in chunks, giving nothing back when a wait ends empty-handed.
 */
final readonly class ChunkedReader
{
    private const int CHUNK_BYTES = 8192;

    /**
     * @param  resource  $handle
     * @return Generator<int, string>
     */
    public static function from(mixed $handle, Duration $wait): Generator
    {
        try {
            stream_set_timeout($handle, $wait->wholeSeconds(), $wait->remainingMicroseconds());

            while (feof($handle) === false) {
                yield (string) fread($handle, self::CHUNK_BYTES);
            }
        } finally {
            fclose($handle);
        }
    }
}
