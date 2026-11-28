<?php

declare(strict_types=1);

use Lemonfiber\Sdk\Client;
use Lemonfiber\Sdk\Exception\Missing;
use Lemonfiber\Sdk\Exception\RequestFailed;
use Lemonfiber\Sdk\Exception\Unreachable;
use Lemonfiber\Sdk\Generated\KeysEnvelope;
use Lemonfiber\Sdk\Http\RevokeKeyRequest;
use Saloon\Enums\Method;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

/**
 * @return array{0: Client, 1: MockClient}
 */
function aClientRevokingWith(MockResponse $answer): array
{
    $mock = new MockClient([RevokeKeyRequest::class => $answer]);
    $client = Client::onPort(9000, 'a-run-token', aWait())->withMockClient($mock);

    return [$client, $mock];
}

/** What revoking raised, or nothing where it came back. */
function whatRevokingRaised(Client $client, string $name): ?Throwable
{
    try {
        $client->keys()->revoke($name);
    } catch (RequestFailed|Unreachable $raised) {
        return $raised;
    }

    return null;
}

it('revokes a key by its name and reads the keys as the stack lists them after', function (): void {
    [$client, $mock] = aClientRevokingWith(MockResponse::make(['api_version' => 1, 'kind' => 'keys', 'data' => ['keys' => []]]));

    $answer = $client->keys()->revoke('home assistant/den');
    $pending = $mock->getLastPendingRequest();

    expect($answer->kind)->toBe('keys')
        ->and((string) $pending?->getUri())->toBe('http://127.0.0.1:9000/api/keys/home%20assistant%2Fden')
        ->and($pending?->getMethod())->toBe(Method::DELETE)
        ->and(theBodySent($mock))->toBe(['dry_run' => false]);
});

it('reads the device key that proved each key, and none for a key minted with the password', function (): void {
    $home = ['name' => 'home', 'scope' => 'read', 'purpose' => 'home-assistant', 'state' => 'active', 'minted' => 1790000000, 'member_minted' => false];
    $listing = ['keys' => [[...$home, 'name' => 'phone', 'proved_by' => 'pixel'], $home], 'purposes' => 'What each key is for is the minter\'s own word.', 'rehearsed' => false];
    [$client] = aClientRevokingWith(MockResponse::make(['api_version' => 1, 'kind' => 'keys', 'data' => $listing]));

    $keys = KeysEnvelope::in($client->keys()->revoke('den'))->data['keys'];

    expect(array_map(static fn(array $key): ?string => $key['proved_by'] ?? null, $keys))->toBe(['pixel', null]);
});

it('raises the stack\'s refusal for a name no key holds, and sends a revoke once', function (): void {
    [$client, $mock] = aClientRevokingWith(MockResponse::make(['api_version' => 1, 'kind' => 'error', 'data' => ['code' => 'KEY-10', 'summary' => 'No key by that name.', 'meaning' => 'Nothing to revoke.', 'severity' => 'error', 'state' => 'actionable', 'remedies' => []]], 404));

    expect(whatRevokingRaised($client, 'gone'))->toBeInstanceOf(Missing::class);
    $mock->assertSentCount(1);
});

it('sends a revoke nothing answered only once', function (): void {
    [$client, $mock] = aClientRevokingWith(MockResponse::make('', 503));

    whatRevokingRaised($client, 'den');

    $mock->assertSentCount(1);
});
