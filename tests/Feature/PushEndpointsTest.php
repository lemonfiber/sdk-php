<?php

declare(strict_types=1);

use Lemonfiber\Sdk\Exception\Busy;
use Lemonfiber\Sdk\Exception\Misasked;
use Lemonfiber\Sdk\Exception\Missing;
use Lemonfiber\Sdk\Exception\RequestFailed;
use Lemonfiber\Sdk\Exception\UnexpectedKind;
use Lemonfiber\Sdk\Exception\Unreachable;
use Lemonfiber\Sdk\Generated\RefusalCode;
use Lemonfiber\Sdk\Http\PushEndpointRequest;
use Lemonfiber\Sdk\PushEndpointRegistering;
use Saloon\Enums\Method;
use Saloon\Http\Faking\MockResponse;

function aPhoneToWake(): PushEndpointRegistering
{
    return PushEndpointRegistering::of('pixel', 'https://push.example/up/a1b2', "\x04\xff\xfe", "\xfb\xff\x01");
}

function aRefusal(string $code, int $status): MockResponse
{
    return MockResponse::make(['api_version' => 1, 'kind' => 'error', 'data' => ['code' => $code, 'summary' => 'Refused.', 'meaning' => 'Nothing was kept.', 'severity' => 'error', 'state' => 'actionable', 'remedies' => []]], $status);
}

it('registers a push endpoint with its name, its address and its keys in base64url', function (): void {
    $registered = ['name' => 'pixel', 'endpoint' => 'https://push.example/up/a1b2', 'registered' => '1762500000', 'rehearsed' => false];
    [$client, $mock] = aClientAnswering(PushEndpointRequest::class, MockResponse::make(['api_version' => 1, 'kind' => 'push-endpoint', 'data' => $registered]));

    $answer = $client->pushEndpoints()->register(aPhoneToWake());
    $pending = $mock->getLastPendingRequest();

    expect($answer->kind)->toBe('push-endpoint')
        ->and($answer->data)->toBe($registered)
        ->and((string) $pending?->getUri())->toBe('http://127.0.0.1:9000/api/push-endpoints')
        ->and($pending?->getMethod())->toBe(Method::POST)
        ->and(theBodySent($mock))->toBe([
            'name' => 'pixel',
            'endpoint' => 'https://push.example/up/a1b2',
            'p256dh' => 'BP_-',
            'auth' => '-_8B',
        ]);
});

it('removes a push endpoint by its name, as one path segment', function (): void {
    $listing = ['endpoints' => [], 'rehearsed' => false, 'removed' => 'pixel/8'];
    [$client, $mock] = aClientAnswering(PushEndpointRequest::class, MockResponse::make(['api_version' => 1, 'kind' => 'push-endpoints', 'data' => $listing]));

    $answer = $client->pushEndpoints()->remove('pixel/8');
    $pending = $mock->getLastPendingRequest();

    expect($answer->kind)->toBe('push-endpoints')
        ->and($answer->data)->toBe($listing)
        ->and((string) $pending?->getUri())->toBe('http://127.0.0.1:9000/api/push-endpoints/pixel%2F8')
        ->and($pending?->getMethod())->toBe(Method::DELETE);
});

it('refuses a registration answered with another kind', function (): void {
    [$client] = aClientAnswering(PushEndpointRequest::class, anEnvelopeOf('push-endpoints'));

    $client->pushEndpoints()->register(aPhoneToWake());
})->throws(UnexpectedKind::class);

it('refuses a removal answered with another kind', function (): void {
    [$client] = aClientAnswering(PushEndpointRequest::class, anEnvelopeOf('push-endpoint'));

    $client->pushEndpoints()->remove('pixel');
})->throws(UnexpectedKind::class);

it('raises each refusal of a registration as its family, by its code, and sends it once', function (string $code, int $status, string $family, RefusalCode $named): void {
    [$client, $mock] = aClientAnswering(PushEndpointRequest::class, aRefusal($code, $status));

    $raised = null;
    try {
        $client->pushEndpoints()->register(aPhoneToWake());
    } catch (RequestFailed|Unreachable $failed) {
        $raised = $failed;
    }

    expect($raised === null ? null : $raised::class)->toBe($family)
        ->and($raised instanceof RequestFailed ? $raised->code() : null)->toBe($named);
    $mock->assertSentCount(1);
})->with([
    'a word that cannot name one' => ['PUSH-1', 400, Misasked::class, RefusalCode::NotAPushName],
    'a name another holds' => ['PUSH-2', 409, Busy::class, RefusalCode::PushNameTaken],
    'an address that is not https' => ['PUSH-3', 400, Misasked::class, RefusalCode::PushNotHttps],
    'keys that cannot be encrypted to' => ['PUSH-4', 400, Misasked::class, RefusalCode::PushKeysUnreadable],
]);

it('raises the removal of a name no endpoint holds as missing, and sends it once', function (): void {
    [$client, $mock] = aClientAnswering(PushEndpointRequest::class, aRefusal('PUSH-5', 404));

    $raised = null;
    try {
        $client->pushEndpoints()->remove('gone');
    } catch (RequestFailed|Unreachable $failed) {
        $raised = $failed;
    }

    expect($raised)->toBeInstanceOf(Missing::class)
        ->and($raised instanceof RequestFailed ? $raised->code() : null)->toBe(RefusalCode::PushNotRegistered);
    $mock->assertSentCount(1);
});

it('sends a registration nothing answered only once', function (): void {
    [$client, $mock] = aClientAnswering(PushEndpointRequest::class, MockResponse::make('', 503));

    try {
        $client->pushEndpoints()->register(aPhoneToWake());
    } catch (RequestFailed|Unreachable) {
    }

    $mock->assertSentCount(1);
});

it('says of a registration its name and none of its keys or its address', function (): void {
    $said = print_r(aPhoneToWake(), true);

    expect($said)->toContain('pixel')
        ->and($said)->not->toContain('push.example')
        ->and($said)->not->toContain("\xfb\xff\x01");
});

it('says where a push-endpoint request goes and nothing it carries', function (): void {
    $said = print_r(PushEndpointRequest::registering(aPhoneToWake()), true);

    expect($said)->toContain('/api/push-endpoints')
        ->and($said)->not->toContain('-_8B')
        ->and($said)->not->toContain('push.example/up');
});
