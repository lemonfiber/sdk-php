<?php

declare(strict_types=1);

use Lemonfiber\Sdk\Contract\Api;
use Lemonfiber\Sdk\Exception\ConfigurationProblem;
use Lemonfiber\Sdk\Http\ActionRequest;
use Lemonfiber\Sdk\Http\IdempotencyKey;

it('travels in the agreed header', function (): void {
    expect(IdempotencyKey::fromString('01J8Z3')->header())
        ->toBe([Api::IDEMPOTENCY_HEADER => '01J8Z3']);
});

it('is spelled the way anything in front of a stack already reads it', function (): void {
    // Asserted as a literal, as the endpoints are: this is the one file where
    // the wire's own spelling lives. A private name would be a header only
    // lemonfiber could ever honour, and nothing between a phone and a stack
    // would know what it was looking at.
    expect(Api::IDEMPOTENCY_HEADER)->toBe('Idempotency-Key')
        ->and(Api::IDEMPOTENCY_HEADER)->not->toStartWith('X-');
});

it('refuses a key that is not there', function (string $given): void {
    // A blank key reaches a stack as no key at all, and the retry it was
    // supposed to make free applies the change a second time.
    expect(fn(): IdempotencyKey => IdempotencyKey::fromString($given))
        ->toThrow(ConfigurationProblem::class, 'this name is blank');
})->with([
    'nothing at all' => [''],
    'only blanks' => ["  \t "],
]);

it('refuses a key carrying characters that cannot travel in a request', function (string $given): void {
    expect(fn(): IdempotencyKey => IdempotencyKey::fromString($given))
        ->toThrow(ConfigurationProblem::class, 'cannot travel in a request');
})->with([
    'a new line' => ["key\nIdempotency-Key: theirs"],
    'a carriage return' => ["key\rIdempotency-Key: theirs"],
    'a null' => ["key\0bad"],
    'a delete' => ["key\x7Fbad"],
]);

it('puts the key on the one request it was given to', function (): void {
    $request = new ActionRequest('/api/actions/down', ['services' => ['sonarr']], IdempotencyKey::fromString('01J8Z3'));

    expect($request->headers()->get(Api::IDEMPOTENCY_HEADER))->toBe('01J8Z3');
});

it('carries no such header where no key was given', function (): void {
    // The absence is asserted rather than assumed. A default key would be one
    // value every action this client ever sent arrived under, which reads to a
    // stack as every one of them being a re-send of the first.
    $request = new ActionRequest('/api/actions/down');

    expect($request->headers()->get(Api::IDEMPOTENCY_HEADER))->toBeNull();
});
