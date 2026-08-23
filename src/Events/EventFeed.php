<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Events;

use Generator;
use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\EnvelopeReader;
use Lemonfiber\Sdk\Exception\ApiVersionMismatch;
use Lemonfiber\Sdk\Exception\ConfigurationProblem;
use Lemonfiber\Sdk\Exception\StreamInterrupted;
use Lemonfiber\Sdk\Exception\UnreadableResponse;

/**
 * Updates across reconnections, marking everything held before a break as out of date.
 */
final readonly class EventFeed
{
    /**
     * @throws ConfigurationProblem
     */
    public function __construct(
        private EventStream $stream,
        private EnvelopeReader $reader,
        private HeldValues $held,
        private int $reconnectLimit,
    ) {
        if ($reconnectLimit < 0) {
            throw ConfigurationProblem::reconnectLimitBelowZero($reconnectLimit);
        }
    }

    /**
     * @return Generator<int, Envelope<mixed>>
     *
     * @throws ApiVersionMismatch
     * @throws StreamInterrupted
     * @throws UnreadableResponse
     */
    public function follow(): Generator
    {
        $lastEventId = null;
        $breaks = 0;

        while (true) {
            try {
                foreach ($this->stream->listen($lastEventId) as $event) {
                    $breaks = 0;
                    $lastEventId = $event->id ?? $lastEventId;

                    $envelope = $this->reader->read($event->data);
                    $this->held->remember($envelope);

                    yield $envelope;
                }
            } catch (StreamInterrupted) {
                $this->held->markGapped();
                $breaks++;

                if ($breaks > $this->reconnectLimit) {
                    throw StreamInterrupted::gaveUpAfter($this->reconnectLimit);
                }
            }
        }
    }

    /**
     * What the stream has shown so far, and whether each part still stands.
     */
    public function held(): HeldValues
    {
        return $this->held;
    }
}
