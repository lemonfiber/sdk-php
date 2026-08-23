<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Exception;

use RuntimeException;

use function sprintf;

/**
 * Live updates stopped arriving.
 */
final class StreamInterrupted extends RuntimeException implements Problem
{
    public static function wentQuiet(int $milliseconds): self
    {
        return new self(sprintf(
            'Live updates stopped arriving. lemonfiber sends a sign of life every %d milliseconds even when nothing has changed, and none arrived, so the connection counts as lost rather than quiet.',
            $milliseconds,
        ));
    }

    public static function ended(): self
    {
        return new self(
            'Live updates ended. The connection closed.',
        );
    }

    public static function neverOpened(): self
    {
        return new self(
            'Live updates could not be opened. The connection handed back nothing to read from.',
        );
    }

    public static function gaveUpAfter(int $reconnections): self
    {
        return new self(sprintf(
            'Live updates were reopened %d times and broke each time, so no further attempt was made. Anything held from before the break is marked out of date.',
            $reconnections,
        ));
    }
}
