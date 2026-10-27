<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Time;

use Closure;

use function hrtime;

/**
 * The machine's own forward-only counter.
 */
final readonly class SystemClock implements Clock
{
    private const int NANOSECONDS_PER_SECOND = 1_000_000_000;

    /** @var Closure(): (int|float) */
    private Closure $nanoseconds;

    /**
     * @param  (Closure(): (int|float))|null  $nanoseconds  the counter read, in nanoseconds; the machine's own where none is given
     */
    public function __construct(?Closure $nanoseconds = null)
    {
        $this->nanoseconds = $nanoseconds ?? static fn(): int|float => hrtime(true);
    }

    public function elapsedSeconds(): float
    {
        return ($this->nanoseconds)() / self::NANOSECONDS_PER_SECOND;
    }
}
