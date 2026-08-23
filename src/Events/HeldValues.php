<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Events;

use Lemonfiber\Sdk\Envelope\Envelope;

/**
 * The last envelope seen for each kind, marked out of date across a break (ARCH-R51).
 */
final class HeldValues
{
    /**
     * @var array<string, HeldValue>
     */
    private array $held = [];

    /**
     * @param Envelope<mixed> $envelope
     */
    public function remember(Envelope $envelope): void
    {
        $this->held[$envelope->kind] = new HeldValue($envelope, Freshness::Current);
    }

    /**
     * Marks everything held so far as gathered before a break.
     */
    public function markGapped(): void
    {
        foreach ($this->held as $kind => $value) {
            $this->held[$kind] = new HeldValue($value->envelope, Freshness::Stale);
        }
    }

    public function get(string $kind): ?HeldValue
    {
        return $this->held[$kind] ?? null;
    }

    /**
     * @return array<string, HeldValue>
     */
    public function all(): array
    {
        return $this->held;
    }
}
