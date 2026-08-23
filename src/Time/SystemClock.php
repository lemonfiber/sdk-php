<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Time;

use function hrtime;

/**
 * The machine's own forward-only counter.
 */
final readonly class SystemClock implements Clock
{
    private const int NANOSECONDS_PER_SECOND = 1_000_000_000;

    public function elapsedSeconds(): float
    {
        return hrtime(true) / self::NANOSECONDS_PER_SECOND;
    }
}
