<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Events;

use Lemonfiber\Sdk\Envelope\Envelope;

/**
 * The last envelope seen for one kind, and whether it still stands.
 */
final readonly class HeldValue
{
    /**
     * @param Envelope<mixed> $envelope
     */
    public function __construct(
        public Envelope $envelope,
        public Freshness $freshness,
    ) {}

    /**
     * Whether this value was gathered before a break in the stream.
     */
    public function isStale(): bool
    {
        return $this->freshness === Freshness::Stale;
    }
}
