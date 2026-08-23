<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Time;

/**
 * A counter that only ever moves forward.
 */
interface Clock
{
    /**
     * Seconds counted so far.
     */
    public function elapsedSeconds(): float;
}
