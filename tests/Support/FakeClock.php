<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Tests\Support;

use function array_shift;

use Lemonfiber\Sdk\Time\Clock;

/**
 * A counter handing out readings that a test decided in advance.
 */
final class FakeClock implements Clock
{
    private float $last = 0.0;

    /**
     * @param  list<float>  $readings
     */
    public function __construct(private array $readings) {}

    public function elapsedSeconds(): float
    {
        $next = array_shift($this->readings);

        if ($next !== null) {
            $this->last = $next;
        }

        return $this->last;
    }
}
