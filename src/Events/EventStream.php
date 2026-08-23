<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Events;

use Generator;
use Lemonfiber\Sdk\Exception\StreamInterrupted;
use Lemonfiber\Sdk\Time\Clock;
use Lemonfiber\Sdk\Time\Duration;

/**
 * One connection's worth of updates, ending as soon as the signs of life stop (ARCH-R50).
 */
final readonly class EventStream
{
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
                if ($now - $lastHeard > $this->heartbeat->inSeconds()) {
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
