<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Http;

use function fclose;
use function feof;
use function fread;

use Generator;
use Lemonfiber\Sdk\Exception\StreamInterrupted;
use Lemonfiber\Sdk\Time\Duration;

use function restore_error_handler;
use function set_error_handler;
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
     *
     * @throws StreamInterrupted
     */
    public static function from(mixed $handle, Duration $wait): Generator
    {
        try {
            stream_set_timeout($handle, $wait->wholeSeconds(), $wait->remainingMicroseconds());

            while (feof($handle) === false) {
                yield self::chunkFrom($handle);
            }
        } finally {
            fclose($handle);
        }
    }

    /**
     * The next chunk, or nothing where the wait ended empty-handed.
     *
     * A connection that breaks while it is being read, such as a network that
     * went away under an encrypted stream, is reported by the platform as a
     * warning as well as by the read's answer. The warning is held back while
     * the read is made, the handler that was in place before is put back
     * afterwards, and the warning is raised as the stream having been
     * interrupted. A read the wait ran out on answers nothing and warns of
     * nothing, and it is the empty chunk it always was.
     *
     * @param  resource  $handle
     *
     * @throws StreamInterrupted
     */
    private static function chunkFrom(mixed $handle): string
    {
        $broke = false;
        set_error_handler(static function () use (&$broke): bool {
            $broke = true;

            return true;
        });

        try {
            $chunk = fread($handle, self::CHUNK_BYTES);
        } finally {
            restore_error_handler();
        }

        if ($broke) {
            throw StreamInterrupted::cutShort();
        }

        return $chunk === false ? '' : $chunk;
    }
}
