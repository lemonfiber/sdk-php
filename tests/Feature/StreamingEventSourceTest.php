<?php

declare(strict_types=1);

use Lemonfiber\Sdk\Contract\Api;
use Lemonfiber\Sdk\Exception\RequestFailed;
use Lemonfiber\Sdk\Http\BaseUrl;
use Lemonfiber\Sdk\Http\LemonfiberConnector;
use Lemonfiber\Sdk\Http\ReadRequest;
use Lemonfiber\Sdk\Http\RunToken;
use Lemonfiber\Sdk\Http\StreamingEventSource;
use Lemonfiber\Sdk\Time\Duration;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

/**
 * @return array{0: StreamingEventSource, 1: MockClient}
 */
function sourceAnswering(MockResponse $answer): array
{
    $mock = new MockClient([ReadRequest::class => $answer]);
    $connector = new LemonfiberConnector(BaseUrl::onPort(9000), RunToken::fromString('a-run-token'));
    $connector->withMockClient($mock);

    return [new StreamingEventSource($connector, Duration::ofMilliseconds(10)), $mock];
}

it('hands out the bytes of the stream', function (): void {
    [$source] = sourceAnswering(MockResponse::make("event: status\ndata: one\n\n"));

    $chunks = iterator_to_array($source->open(null), false);

    expect(implode('', $chunks))->toBe("event: status\ndata: one\n\n");
});

it('asks the events endpoint for a stream, carrying the token', function (): void {
    [$source, $mock] = sourceAnswering(MockResponse::make(''));

    iterator_to_array($source->open(null), false);

    $pending = $mock->getLastPendingRequest();

    expect((string) $pending?->getUri())->toBe('http://127.0.0.1:9000' . Api::EVENTS_ENDPOINT)
        ->and($pending?->headers()->get('Accept'))->toBe(Api::EVENT_STREAM_MEDIA_TYPE)
        ->and($pending?->headers()->get(Api::TOKEN_HEADER))->toBe('a-run-token')
        ->and($pending?->config()->get('stream'))->toBeTrue()
        ->and($pending?->headers()->get(Api::RESUME_HEADER))->toBeNull();
});

it('names the event it is resuming from', function (): void {
    [$source, $mock] = sourceAnswering(MockResponse::make(''));

    iterator_to_array($source->open('42'), false);

    expect($mock->getLastPendingRequest()?->headers()->get(Api::RESUME_HEADER))->toBe('42');
});

it('reports a stream that was turned down', function (): void {
    [$source] = sourceAnswering(MockResponse::make('{}', 403));

    expect(fn(): Iterator => $source->open(null))
        ->toThrow(RequestFailed::class, 'turned down the request for /api/events and answered 403');
});

it('hands the caller the sentence a stream was refused with', function (): void {
    $said = 'This request carried no token, or not this run\'s.';

    [$source] = sourceAnswering(MockResponse::make($said, 403));

    $problem = null;

    try {
        $source->open(null);
    } catch (RequestFailed $refusal) {
        $problem = $refusal;
    }

    expect($problem?->getMessage())->toBe($said)
        ->and($problem?->said())->toBe($said)
        ->and($problem?->status())->toBe(403);
});
