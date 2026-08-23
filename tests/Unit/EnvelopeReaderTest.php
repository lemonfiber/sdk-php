<?php

declare(strict_types=1);

use Lemonfiber\Sdk\Contract\Api;
use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\EnvelopeReader;
use Lemonfiber\Sdk\Exception\ApiVersionMismatch;
use Lemonfiber\Sdk\Exception\UnreadableResponse;
use Lemonfiber\Sdk\Generated\Contract;

it('reads the wrapper lemonfiber answers in', function (): void {
    $envelope = new EnvelopeReader()->read('{"api_version":1,"kind":"status","data":{"health":"healthy"}}');

    expect($envelope->apiVersion)->toBe(1)
        ->and($envelope->kind)->toBe('status')
        ->and($envelope->data)->toBe(['health' => 'healthy']);
});

it('reads a payload that is not an object', function (): void {
    $envelope = new EnvelopeReader()->read('{"api_version":1,"kind":"version","data":7}');

    expect($envelope->kind)->toBe('version')
        ->and($envelope->data)->toBe(7);
});

it('reads a payload that is nothing at all', function (): void {
    expect(new EnvelopeReader()->read('{"api_version":1,"kind":"status","data":null}')->data)->toBeNull();
});

it('speaks the version the generated contract names, and states it once', function (): void {
    expect(Api::VERSION)->toBe(Contract::API_VERSION);
});

it('refuses an answer speaking another version, naming both', function (): void {
    expect(fn(): Envelope => new EnvelopeReader()->read('{"api_version":2,"kind":"status","data":{}}'))
        ->toThrow(ApiVersionMismatch::class, 'speaks version 1 of the lemonfiber API and the answer came back as version 2');
});

it('refuses a mismatched version before reading anything else', function (): void {
    $thrown = null;

    try {
        new EnvelopeReader()->read('{"api_version":9,"data":null}');
    } catch (Throwable $caught) {
        $thrown = $caught;
    }

    expect($thrown)->toBeInstanceOf(ApiVersionMismatch::class);
});

it('reads against a version a caller names', function (): void {
    expect(new EnvelopeReader(3)->read('{"api_version":3,"kind":"status","data":{}}')->apiVersion)->toBe(3);
});

it('refuses an answer it cannot read', function (string $body, string $expected): void {
    expect(fn(): Envelope => new EnvelopeReader()->read($body))
        ->toThrow(UnreadableResponse::class, $expected);
})->with([
    'not json at all' => ['<html></html>', 'not readable as JSON'],
    'json but not a wrapper' => ['"a bare string"', 'not the wrapper'],
    'a number' => ['7', 'not the wrapper'],
    'no version' => ['{"kind":"status","data":{}}', 'no whole-number api_version'],
    'a version that is not a whole number' => ['{"api_version":"1","kind":"status","data":{}}', 'no whole-number api_version'],
    'no kind' => ['{"api_version":1,"data":{}}', 'carries no kind'],
    'an empty kind' => ['{"api_version":1,"kind":"","data":{}}', 'carries no kind'],
    'a kind that is not words' => ['{"api_version":1,"kind":4,"data":{}}', 'carries no kind'],
    'no data' => ['{"api_version":1,"kind":"status"}', 'carries no data'],
]);
