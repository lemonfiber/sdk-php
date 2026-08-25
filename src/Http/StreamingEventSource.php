<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Http;

use Iterator;
use Lemonfiber\Sdk\Contract\Api;
use Lemonfiber\Sdk\Events\EventSource;
use Lemonfiber\Sdk\Exception\RequestFailed;
use Lemonfiber\Sdk\Exception\StreamInterrupted;
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
     */
    public function open(?string $lastEventId): Iterator
    {
        $request = new ReadRequest(Api::EVENTS_ENDPOINT);
        $request->headers()->add('Accept', Api::EVENT_STREAM_MEDIA_TYPE);
        $request->config()->add('stream', true);

        if ($lastEventId !== null) {
            $request->headers()->add(Api::RESUME_HEADER, $lastEventId);
        }

        $response = $this->connector->send($request);

        if ($response->failed()) {
            throw RequestFailed::from(Api::EVENTS_ENDPOINT, $response->status(), $response->body());
        }

        return ChunkedReader::from(StreamHandle::beneath($response->stream()), $this->wait);
    }
}
