<?php

declare(strict_types=1);

use Lemonfiber\Sdk\Client;
use Lemonfiber\Sdk\Exception\ConfigurationProblem;
use Lemonfiber\Sdk\Http\ForgetRequest;
use Saloon\Http\Faking\MockResponse;

it('sends a job name holding what a path would read as more than one segment as one', function (): void {
    [$client, $mock] = clientAnswering([
        ForgetRequest::class => MockResponse::make(
            '{"api_version":1,"kind":"job","data":{"job":"a","action":"repair"}}',
            202,
            ['Content-Type' => 'application/json'],
        ),
    ]);

    $client->forget('../a/b?c=d#e%');

    expect((string) $mock->getLastPendingRequest()?->getUri())
        ->toBe('http://127.0.0.1:9000/api/jobs/..%2Fa%2Fb%3Fc%3Dd%23e%25/forget');
});

it('refuses a name that is no path segment, sending nothing', function (string $name): void {
    [$client, $mock] = clientAnswering([]);
    $calls = [
        'asking after work' => static fn(Client $client): mixed => $client->whatBecameOf($name),
        'releasing work' => static fn(Client $client): mixed => $client->letGoOf($name),
        'forgetting work' => static fn(Client $client): mixed => $client->forget($name),
        'a support bundle' => static fn(Client $client): mixed => $client->bundle($name),
        'revoking a key' => static fn(Client $client): mixed => $client->keys()->revoke($name),
        'challenging a device key' => static fn(Client $client): mixed => $client->keys()->deviceChallenge($name),
        'revoking a device key' => static fn(Client $client): mixed => $client->keys()->revokeDevice($name),
        'removing a push endpoint' => static fn(Client $client): mixed => $client->pushEndpoints()->remove($name),
    ];
    foreach ($calls as $call) {
        expect(fn(): mixed => $call($client))->toThrow(ConfigurationProblem::class, 'it names no path segment');
    }
    expect($mock->getRecordedResponses())->toBe([]);
})->with(['empty' => [''], 'the folder itself' => ['.'], 'the one above it' => ['..']]);
