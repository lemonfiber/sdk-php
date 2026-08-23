<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Time;

use function intdiv;

use Lemonfiber\Sdk\Exception\ConfigurationProblem;

/**
 * A length of time, held in milliseconds.
 */
final readonly class Duration
{
    private const int MILLISECONDS_PER_SECOND = 1000;

    private const int MICROSECONDS_PER_MILLISECOND = 1000;

    private function __construct(public int $milliseconds) {}

    /**
     * @throws ConfigurationProblem
     */
    public static function ofMilliseconds(int $milliseconds): self
    {
        if ($milliseconds <= 0) {
            throw ConfigurationProblem::lengthOfTimeNotPositive($milliseconds);
        }

        return new self($milliseconds);
    }

    /**
     * @throws ConfigurationProblem
     */
    public static function ofSeconds(int $seconds): self
    {
        return self::ofMilliseconds($seconds * self::MILLISECONDS_PER_SECOND);
    }

    /**
     * Whole seconds within this length of time.
     */
    public function wholeSeconds(): int
    {
        return intdiv($this->milliseconds, self::MILLISECONDS_PER_SECOND);
    }

    /**
     * Microseconds left over once the whole seconds are taken out.
     */
    public function remainingMicroseconds(): int
    {
        return ($this->milliseconds % self::MILLISECONDS_PER_SECOND) * self::MICROSECONDS_PER_MILLISECOND;
    }

    /**
     * This length of time as a number of seconds.
     */
    public function inSeconds(): float
    {
        return $this->milliseconds / self::MILLISECONDS_PER_SECOND;
    }
}
