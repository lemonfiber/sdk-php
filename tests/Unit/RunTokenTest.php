<?php

declare(strict_types=1);

use Lemonfiber\Sdk\Contract\Api;
use Lemonfiber\Sdk\Exception\ConfigurationProblem;
use Lemonfiber\Sdk\Http\BaseUrl;
use Lemonfiber\Sdk\Http\LemonfiberConnector;
use Lemonfiber\Sdk\Http\ReadRequest;
use Lemonfiber\Sdk\Http\RunToken;
use Saloon\Http\PendingRequest;

it('puts the token in the agreed header', function (): void {
    $token = RunToken::fromString('a-run-token');
    $connector = new LemonfiberConnector(BaseUrl::onPort(9000), $token);
    $pending = new PendingRequest($connector, new ReadRequest('/api/status'));

    $token->set($pending);

    expect($pending->headers()->get(Api::TOKEN_HEADER))->toBe('a-run-token');
});

it('refuses a token that is not there', function (string $given): void {
    expect(fn(): RunToken => RunToken::fromString($given))
        ->toThrow(ConfigurationProblem::class, 'No run token was given');
})->with([
    'nothing at all' => [''],
    'only blanks' => ["  \t "],
]);

it('refuses a token carrying characters that cannot travel in a request', function (string $given): void {
    expect(fn(): RunToken => RunToken::fromString($given))
        ->toThrow(ConfigurationProblem::class, 'cannot travel in a request');
})->with([
    'a new line' => ["good\nX-Injected: yes"],
    'a carriage return' => ["good\rX-Injected: yes"],
    'a null' => ["good\0bad"],
    'a delete' => ["good\x7Fbad"],
]);

it('keeps the token out of anything that prints it', function (): void {
    $token = RunToken::fromString('a-run-token');

    expect($token->__debugInfo())->toBe(['value' => '(hidden)'])
        ->and(print_r($token, true))->not->toContain('a-run-token');
});
