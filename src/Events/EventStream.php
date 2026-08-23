<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Events;

use Generator;
use Lemonfiber\Sdk\Exception\StreamInterrupted;
use Lemonfiber\Sdk\Time\Clock;
use Lemonfiber\Sdk\Time\Duration;

/**
 * One connection's worth of updates, ending as soon as the signs of life stop.
 */
final readonly class EventStream
{
    /**
     * How many beats of silence a stream is given before it is called broken.
     *
     * Two, not one. A single missed beat is far more often a slow moment than a
     * dead connection, and the server's beat is what a client measures against
     * rather than a promise about the network underneath it. Ending the stream
     * on the first miss is the thing the doubling exists to prevent.
     */
    private const int SILENCE_ALLOWED = 2;

    public function __construct(
        private EventSource $source,
        private Clock $clock,
        private Duration $heartbeat,
    ) {}

    /**
     * @return Generator<int, ServerEvent>
     *
     * @throws StreamInterrupted
     */
    public function listen(?string $lastEventId = null): Generator
    {
        $parser = new SseParser($lastEventId);
        $lastHeard = $this->clock->elapsedSeconds();

        foreach ($this->source->open($lastEventId) as $chunk) {
            $now = $this->clock->elapsedSeconds();

            if ($chunk === '') {
                if ($now - $lastHeard > $this->heartbeat->inSeconds() * self::SILENCE_ALLOWED) {
                    throw StreamInterrupted::wentQuiet($this->heartbeat->milliseconds);
                }

                continue;
            }

            $lastHeard = $now;

            foreach ($parser->feed($chunk) as $event) {
                yield $event;
            }
        }

        throw StreamInterrupted::ended();
    }
}
