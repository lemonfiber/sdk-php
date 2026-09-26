<?php

declare(strict_types=1);

use Lemonfiber\Sdk\BundleFile;
use Lemonfiber\Sdk\Client;
use Lemonfiber\Sdk\Contract\Api;
use Lemonfiber\Sdk\Exception\RequestFailed;
use Lemonfiber\Sdk\Http\ReadRequest;
use Saloon\Enums\Method;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

/**
 * The bytes of a gzip archive holding nothing, as a bundle arrives.
 */
const AN_ARCHIVE = "\x1f\x8b\x08\x00\x00\x00\x00\x00\x00\x03\x03\x00\x00\x00\x00\x00\x00\x00\x00\x00";

/**
 * @return array{0: Client, 1: MockClient}
 */
function aClientHandingOver(MockResponse $answer): array
{
    $mock = new MockClient([ReadRequest::class => $answer]);
    $client = Client::onPort(9000, 'a-run-token');
    $client->connector()->withMockClient($mock);

    return [$client, $mock];
}

/**
 * What asking for a bundle raised, or nothing where it came back.
 */
function whatFetchingRaised(Client $client, string $name): ?RequestFailed
{
    try {
        $client->bundle($name);
    } catch (RequestFailed $refusal) {
        return $refusal;
    }

    return null;
}

it('fetches a bundle by name and hands back its bytes and what the transport said of them', function (): void {
    [$client, $mock] = aClientHandingOver(MockResponse::make(AN_ARCHIVE, 200, [
        'Content-Type' => 'application/gzip',
        'Content-Length' => '20',
        'Content-Disposition' => 'attachment',
    ]));

    $bundle = $client->bundle('t4m8');
    $pending = $mock->getLastPendingRequest();

    expect($bundle)->toBeInstanceOf(BundleFile::class)
        ->and($bundle->name())->toBe('t4m8')
        ->and($bundle->bytes())->toBe(AN_ARCHIVE)
        ->and($bundle->contentType())->toBe('application/gzip')
        ->and($bundle->length())->toBe(20)
        ->and((string) $pending?->getUri())->toBe('http://127.0.0.1:9000/api/bundle/t4m8')
        ->and($pending?->getMethod())->toBe(Method::GET)
        ->and($pending?->headers()->get(Api::TOKEN_HEADER))->toBe('a-run-token');
});

it('states no type and no length where the answer carried neither', function (): void {
    [$client] = aClientHandingOver(MockResponse::make(AN_ARCHIVE));

    $bundle = $client->bundle('t4m8');

    expect($bundle->bytes())->toBe(AN_ARCHIVE)
        ->and($bundle->contentType())->toBeNull()
        ->and($bundle->length())->toBeNull();
});

it('states no length where the header holds something other than a whole number', function (string $length): void {
    [$client] = aClientHandingOver(MockResponse::make(AN_ARCHIVE, 200, ['Content-Length' => $length]));

    expect($client->bundle('t4m8')->length())->toBeNull();
})->with(['a word' => ['twenty'], 'a negative' => ['-20'], 'a fraction' => ['2.5'], 'nothing' => ['']]);

it('reads a stated length of zero as a length', function (): void {
    [$client] = aClientHandingOver(MockResponse::make('', 200, ['Content-Length' => '0']));

    expect($client->bundle('t4m8')->length())->toBe(0);
});

it('raises the refusal a name nobody kept is answered with, in lemonfiber\'s words', function (): void {
    $said = '`t4m8` is not one of the bundles kept here';

    [$client] = aClientHandingOver(MockResponse::make(
        json_encode(['api_version' => 1, 'kind' => 'error', 'data' => ['summary' => $said]], JSON_THROW_ON_ERROR),
        404,
        ['Content-Type' => 'application/json'],
    ));

    $refusal = whatFetchingRaised($client, 't4m8');

    expect($refusal?->said())->toBe($said)
        ->and($refusal?->getMessage())->toBe($said)
        ->and($refusal?->status())->toBe(404)
        ->and($refusal?->endpoint())->toBe('/api/bundle/t4m8');
});

it('raises a refusal said in prose, and a failure of the stack itself', function (int $status, string $body, ?string $said): void {
    [$client] = aClientHandingOver(MockResponse::make($body, $status, ['Content-Type' => 'text/plain; charset=utf-8']));

    $refusal = whatFetchingRaised($client, 'w2qr');

    expect($refusal?->said())->toBe($said)
        ->and($refusal?->status())->toBe($status)
        ->and($refusal?->endpoint())->toBe('/api/bundle/w2qr');
})->with([
    'a missing token' => [401, 'This needs the run token.', 'This needs the run token.'],
    'a broken stack' => [500, '', null],
]);
