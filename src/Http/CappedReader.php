<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Http;

use Psr\Http\Message\StreamInterface;

use function strlen;

/**
 * A body read no further than one byte past the most it may be.
 */
final readonly class CappedReader
{
    /**
     * The body's bytes, up to one past `$most`, with the stream closed after them so
     * whatever is left is never read.
     *
     * @param non-negative-int $most
     */
    public static function upTo(StreamInterface $stream, int $most): string
    {
        $read = '';
        while (! $stream->eof() && strlen($read) <= $most) {
            $read .= $stream->read($most + 1 - strlen($read));
        }

        $stream->close();

        return $read;
    }
}
