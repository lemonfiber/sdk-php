<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Http;

use function is_resource;

use Lemonfiber\Sdk\Exception\StreamInterrupted;
use Psr\Http\Message\StreamInterface;

/**
 * The readable handle underneath a response body.
 */
final readonly class StreamHandle
{
    /**
     * @return resource
     *
     * @throws StreamInterrupted
     */
    public static function beneath(StreamInterface $stream): mixed
    {
        $handle = $stream->detach();

        if (! is_resource($handle)) {
            throw StreamInterrupted::neverOpened();
        }

        return $handle;
    }
}
