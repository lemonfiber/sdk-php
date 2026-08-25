<?php

declare(strict_types=1);

use Lemonfiber\Sdk\Client;
use Lemonfiber\Sdk\Contract\Api;
use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Events\EventFeed;
use Lemonfiber\Sdk\Exception\ConfigurationProblem;
use Lemonfiber\Sdk\Exception\RequestFailed;
use Lemonfiber\Sdk\Http\ActionRequest;
use Lemonfiber\Sdk\Http\ReadRequest;
use Lemonfiber\Sdk\Time\Duration;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

const A_RUN_TOKEN = 'a-run-token';

/**
 * @param  array<class-string, MockResponse>  $answers
 * @return array{0: Client, 1: MockClient}
 */
function clientAnswering(array $answers): array
{
    $mock = new MockClient($answers);
    $client = Client::onPort(9000, A_RUN_TOKEN);
    $client->connector()->withMockClient($mock);

    return [$client, $mock];
}

it('reads an endpoint and hands back the envelope', function (): void {
    [$client] = clientAnswering([
        ReadRequest::class => MockResponse::make('{"api_version":1,"kind":"status","data":{"health":"healthy"}}'),
    ]);

    $envelope = $client->read('/api/status');

    expect($envelope->kind)->toBe('status')
        ->and($envelope->data)->toBe(['health' => 'healthy']);
});

it('sends the token as a header and never in the address', function (): void {
    [$client, $mock] = clientAnswering([
        ReadRequest::class => MockResponse::make('{"api_version":1,"kind":"logs","data":[]}'),
    ]);

    $client->read('/api/logs', ['since' => 'yesterday', 'lines' => 20]);

    $pending = $mock->getLastPendingRequest();
    $address = (string) $pending?->getUri();

    expect($pending?->headers()->get(Api::TOKEN_HEADER))->toBe(A_RUN_TOKEN)
        ->and($address)->toBe('http://127.0.0.1:9000/api/logs?since=yesterday&lines=20')
        ->and($address)->not->toContain(A_RUN_TOKEN)
        ->and($address)->not->toContain(Api::TOKEN_HEADER);
});

it('asks for the answer as json', function (): void {
    [$client, $mock] = clientAnswering([
        ReadRequest::class => MockResponse::make('{"api_version":1,"kind":"status","data":{}}'),
    ]);

    $client->read('/api/status');

    expect($mock->getLastPendingRequest()?->headers()->get('Accept'))->toBe('application/json');
});

it('acts on an endpoint, carrying its payload as json', function (): void {
    [$client, $mock] = clientAnswering([
        ActionRequest::class => MockResponse::make('{"api_version":1,"kind":"job","data":{"id":"j1"}}'),
    ]);

    $envelope = $client->act('/api/actions/retry-import', ['service' => 'sonarr']);

    $pending = $mock->getLastPendingRequest();

    expect($envelope->kind)->toBe('job')
        ->and($pending?->body()?->all())->toBe(['service' => 'sonarr'])
        ->and($pending?->headers()->get(Api::TOKEN_HEADER))->toBe(A_RUN_TOKEN)
        ->and((string) $pending?->getUri())->toBe('http://127.0.0.1:9000/api/actions/retry-import');
});

it('reports an endpoint that was turned down, reading nothing from it', function (): void {
    [$client] = clientAnswering([
        ReadRequest::class => MockResponse::make('{"api_version":1,"kind":"error","data":{}}', 401),
    ]);

    expect(fn(): Envelope => $client->read('/api/status'))
        ->toThrow(RequestFailed::class, 'turned down the request for /api/status and answered 401');
});

it('reports an action that was turned down', function (): void {
    [$client] = clientAnswering([
        ActionRequest::class => MockResponse::make('{}', 500),
    ]);

    expect(fn(): Envelope => $client->act('/api/actions/repair'))
        ->toThrow(RequestFailed::class, 'answered 500');
});

it('hands the caller the sentence a read was refused with', function (): void {
    $said = 'That is not a group of checks lemonfiber knows.';

    [$client] = clientAnswering([
        ReadRequest::class => MockResponse::make($said, 400),
    ]);

    $problem = null;

    try {
        $client->read('/api/doctor', ['only' => 'nope']);
    } catch (RequestFailed $refusal) {
        $problem = $refusal;
    }

    expect($problem?->getMessage())->toBe($said)
        ->and($problem?->said())->toBe($said)
        ->and($problem?->status())->toBe(400)
        ->and($problem?->endpoint())->toBe('/api/doctor');
});

it('is built from a written out address', function (): void {
    $client = Client::at('http://127.0.0.1:9000', A_RUN_TOKEN);

    expect($client->connector()->resolveBaseUrl())->toBe('http://127.0.0.1:9000');
});

it('refuses to be built against another machine', function (): void {
    expect(fn(): Client => Client::at('http://example.com:9000', A_RUN_TOKEN))
        ->toThrow(ConfigurationProblem::class, 'points somewhere else');
});

it('refuses to be built without a token', function (): void {
    expect(fn(): Client => Client::onPort(9000, ''))
        ->toThrow(ConfigurationProblem::class, 'No run token was given');
});

it('waits as long as it was told between reads, or a quarter second', function (): void {
    $client = Client::onPort(9000, A_RUN_TOKEN);

    expect($client->eventSource()->wait->milliseconds)->toBe(250)
        ->and($client->eventSource(Duration::ofMilliseconds(75))->wait->milliseconds)->toBe(75);
});

it('opens a feed of live updates', function (): void {
    $client = Client::onPort(9000, A_RUN_TOKEN);

    expect($client->events(Duration::ofSeconds(15)))->toBeInstanceOf(EventFeed::class)
        ->and($client->events(Duration::ofSeconds(15), Duration::ofMilliseconds(50), 2))->toBeInstanceOf(EventFeed::class);
});
