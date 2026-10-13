<?php

declare(strict_types=1);

use Lemonfiber\Sdk\Client;
use Lemonfiber\Sdk\DeviceAttestation;
use Lemonfiber\Sdk\Exception\Missing;
use Lemonfiber\Sdk\Exception\RequestFailed;
use Lemonfiber\Sdk\Exception\Unreachable;
use Lemonfiber\Sdk\Http\DeviceKeyRequest;
use Lemonfiber\Sdk\Http\MintKeyRequest;
use Lemonfiber\Sdk\KeyMint;
use Lemonfiber\Sdk\MintProof;
use Saloon\Enums\Method;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Repositories\Body\JsonBodyRepository;

/**
 * @return array{0: Client, 1: MockClient}
 */
function aClientAnswering(string $request, MockResponse $answer): array
{
    $mock = new MockClient([$request => $answer]);

    return [Client::onPort(9000, 'a-run-token', aWait())->withMockClient($mock), $mock];
}

/**
 * The body the last request carried, decoded.
 *
 * @return array<mixed>
 */
function theBodySent(MockClient $mock): array
{
    $body = $mock->getLastPendingRequest()?->body();
    $sent = json_decode($body instanceof JsonBodyRepository ? (string) $body : '', true, flags: JSON_THROW_ON_ERROR);

    return is_array($sent) ? $sent : [];
}

/** A fresh P-256 key pair, as a phone's hardware would hold one. */
function aPhone(): OpenSSLAsymmetricKey
{
    $phone = openssl_pkey_new(['private_key_type' => OPENSSL_KEYTYPE_EC, 'curve_name' => 'prime256v1']);
    if (! $phone instanceof OpenSSLAsymmetricKey) {
        throw new RuntimeException('This PHP makes no P-256 key pairs.');
    }

    return $phone;
}

function anEnvelopeOf(string $kind): MockResponse
{
    return MockResponse::make(['api_version' => 1, 'kind' => $kind, 'data' => []]);
}

it('registers a device key with its public half, its attestation and the password, in base64url', function (): void {
    [$client, $mock] = aClientAnswering(DeviceKeyRequest::class, anEnvelopeOf('device-key'));

    $answer = $client->registerDeviceKey('pixel', "\x30\xff\xfe", DeviceAttestation::android(["\xfb\xff", "\x01"]), 'the password');
    $pending = $mock->getLastPendingRequest();

    expect($answer->kind)->toBe('device-key')
        ->and((string) $pending?->getUri())->toBe('http://127.0.0.1:9000/api/device-keys')
        ->and($pending?->getMethod())->toBe(Method::POST)
        ->and(theBodySent($mock))->toBe([
            'name' => 'pixel',
            'public_key' => 'MP_-',
            'attestation' => ['platform' => 'android', 'chain' => ['-_8', 'AQ']],
            'password' => 'the password',
        ]);
});

it('carries an iOS attestation as its object in base64url', function (): void {
    [$client, $mock] = aClientAnswering(DeviceKeyRequest::class, anEnvelopeOf('device-key'));

    $client->registerDeviceKey('iphone', 'key', DeviceAttestation::ios("\xa3\x63"), 'the password');

    expect(theBodySent($mock)['attestation'])->toBe(['platform' => 'ios', 'object' => 'o2M']);
});

it('asks a challenge for one device key by its name, as one path segment', function (): void {
    [$client, $mock] = aClientAnswering(DeviceKeyRequest::class, anEnvelopeOf('device-challenge'));

    $answer = $client->deviceKeyChallenge('pixel/8');
    $pending = $mock->getLastPendingRequest();

    expect($answer->kind)->toBe('device-challenge')
        ->and((string) $pending?->getUri())->toBe('http://127.0.0.1:9000/api/device-keys/pixel%2F8/challenge')
        ->and($pending?->getMethod())->toBe(Method::POST);
});

it('revokes a device key by its name', function (): void {
    [$client, $mock] = aClientAnswering(DeviceKeyRequest::class, anEnvelopeOf('device-keys'));

    $answer = $client->revokeDeviceKey('pixel 8');
    $pending = $mock->getLastPendingRequest();

    expect($answer->kind)->toBe('device-keys')
        ->and((string) $pending?->getUri())->toBe('http://127.0.0.1:9000/api/device-keys/pixel%208')
        ->and($pending?->getMethod())->toBe(Method::DELETE);
});

it('raises the stack\'s refusal for a device key no longer registered, and sends it once', function (): void {
    [$client, $mock] = aClientAnswering(DeviceKeyRequest::class, MockResponse::make(['api_version' => 1, 'kind' => 'error', 'data' => ['code' => 'DEVICE-9', 'summary' => 'No device key by that name.', 'meaning' => 'Nothing to prove with.', 'severity' => 'error', 'state' => 'actionable', 'remedies' => []]], 404));

    $raised = null;
    try {
        $client->deviceKeyChallenge('gone');
    } catch (RequestFailed|Unreachable $failed) {
        $raised = $failed;
    }

    expect($raised)->toBeInstanceOf(Missing::class);
    $mock->assertSentCount(1);
});

it('mints with the password given again', function (): void {
    [$client, $mock] = aClientAnswering(MintKeyRequest::class, anEnvelopeOf('minted-key'));

    $answer = $client->mintKey(KeyMint::of('den', 'read', 'home-assistant'), MintProof::password('the password'));
    $pending = $mock->getLastPendingRequest();

    expect($answer->kind)->toBe('minted-key')
        ->and((string) $pending?->getUri())->toBe('http://127.0.0.1:9000/api/keys')
        ->and($pending?->getMethod())->toBe(Method::POST)
        ->and(theBodySent($mock))->toBe(['name' => 'den', 'scope' => 'read', 'purpose' => 'home-assistant', 'claim' => false, 'password' => 'the password']);
});

it('mints with a device key\'s signature over the text the stack verifies, and no password', function (): void {
    [$client, $mock] = aClientAnswering(MintKeyRequest::class, anEnvelopeOf('key-claim'));
    $phone = aPhone();
    $mint = KeyMint::of('den', 'act', 'mcp', claim: true);
    openssl_sign($mint->signedText('a-challenge'), $signature, $phone, OPENSSL_ALGO_SHA256);

    $client->mintKey($mint, MintProof::device('pixel', 'a-challenge', is_string($signature) ? $signature : ''));
    $sent = theBodySent($mock);
    $carried = is_string($sent['signature'] ?? null) ? $sent['signature'] : '';
    $details = openssl_pkey_get_details($phone);
    $public = is_array($details) && is_string($details['key'] ?? null) ? $details['key'] : '';

    expect($sent)->not->toHaveKey('password');
    expect($sent['device'] ?? null)->toBe('pixel')
        ->and($sent['challenge'] ?? null)->toBe('a-challenge')
        ->and($sent['claim'] ?? null)->toBeTrue()
        ->and($carried)->toMatch('/^[A-Za-z0-9_-]+$/')
        ->and(openssl_verify("lemonfiber-mint/1\na-challenge\nden\nact\nmcp\nclaim\n", (string) base64_decode(strtr($carried, '-_', '+/'), true), $public, OPENSSL_ALGO_SHA256))->toBe(1);
});

it('sends a mint nothing answered only once', function (): void {
    [$client, $mock] = aClientAnswering(MintKeyRequest::class, MockResponse::make('', 503));

    try {
        $client->mintKey(KeyMint::of('den', 'read', 'other'), MintProof::password('the password'));
    } catch (RequestFailed|Unreachable) {
    }

    $mock->assertSentCount(1);
});

it('says where a device-key request goes and nothing it carries', function (): void {
    $said = print_r(DeviceKeyRequest::registering('pixel', 'key', DeviceAttestation::ios('object'), 'the password'), true);

    expect($said)->toContain('/api/device-keys');
    expect($said)->not->toContain('the password');
});
