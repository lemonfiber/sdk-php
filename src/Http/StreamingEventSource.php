<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Http;

use Iterator;
use Lemonfiber\Sdk\Events\EventSource;
use Lemonfiber\Sdk\Exception\RequestFailed;
use Lemonfiber\Sdk\Exception\StreamInterrupted;
use Lemonfiber\Sdk\Exception\Unreachable;
use Lemonfiber\Sdk\Time\Duration;

/**
 * Live updates read from lemonfiber over HTTP.
 */
final readonly class StreamingEventSource implements EventSource
{
    public function __construct(
        private LemonfiberConnector $connector,
        public Duration $wait,
    ) {}

    /**
     * @return Iterator<int, string>
     *
     * @throws RequestFailed
     * @throws StreamInterrupted
     * @throws Unreachable
     */
    public function open(?string $lastEventId): Iterator
    {
        return $this->connector->answer(ReadRequest::events($lastEventId, $this->wait));
    }
}
