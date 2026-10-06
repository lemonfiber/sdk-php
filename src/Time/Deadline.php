<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Time;

use function max;

/**
 * How long one call may still take, counted from the moment it began.
 *
 * A call is every attempt at one request: a read asked again after nothing
 * answered shares the wait the first attempt began. The bound is how long somebody waits for an answer,
 * which is the whole call rather than any one attempt at it, so each attempt
 * is given what is left and a further attempt is made only while there is
 * room for it.
 */
final readonly class Deadline
{
    private function __construct(private Clock $clock, private float $endsAt) {}

    /**
     * A wait of this length, beginning now.
     */
    public static function after(Duration $wait, Clock $clock): self
    {
        return new self($clock, $clock->elapsedSeconds() + $wait->inSeconds());
    }

    /**
     * Seconds left before the wait is over, and none once it is.
     */
    public function secondsLeft(): float
    {
        return max(0.0, $this->endsAt - $this->clock->elapsedSeconds());
    }

    /**
     * Whether pausing this many seconds still leaves time to ask again.
     */
    public function hasRoomFor(float $pause): bool
    {
        return $this->secondsLeft() > $pause;
    }
}
