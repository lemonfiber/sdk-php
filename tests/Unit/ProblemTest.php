<?php

declare(strict_types=1);

use Lemonfiber\Sdk\Exception\ApiVersionMismatch;
use Lemonfiber\Sdk\Exception\ConfigurationProblem;
use Lemonfiber\Sdk\Exception\Problem;
use Lemonfiber\Sdk\Exception\RequestFailed;
use Lemonfiber\Sdk\Exception\StreamInterrupted;
use Lemonfiber\Sdk\Exception\UnexpectedKind;
use Lemonfiber\Sdk\Exception\UnreadableResponse;

it('names both versions when they disagree', function (): void {
    $problem = ApiVersionMismatch::between(1, 4);

    expect($problem->spoken())->toBe(1)
        ->and($problem->answered())->toBe(4)
        ->and($problem->getMessage())->toContain('speaks version 1')
        ->and($problem->getMessage())->toContain('came back as version 4')
        ->and($problem->getMessage())->toContain('Nothing was taken from that answer');
});

it('says which endpoint was turned down and how', function (): void {
    $problem = RequestFailed::from('/api/status', 401, '');

    expect($problem->endpoint())->toBe('/api/status')
        ->and($problem->status())->toBe(401)
        ->and($problem->getMessage())->toBe(
            'lemonfiber turned down the request for /api/status and answered 401. Nothing was taken from that answer.',
        );
});

it('describes every way a stream can stop', function (): void {
    expect(StreamInterrupted::wentQuiet(15_000)->getMessage())->toContain('every 15000 milliseconds')
        ->and(StreamInterrupted::ended()->getMessage())->toContain('The connection closed')
        ->and(StreamInterrupted::neverOpened()->getMessage())->toContain('handed back nothing to read from')
        ->and(StreamInterrupted::gaveUpAfter(3)->getMessage())->toContain('reopened 3 times');
});

it('describes every way an answer can be unreadable', function (): void {
    expect(UnreadableResponse::notJson('Syntax error')->getMessage())->toContain('not readable as JSON: Syntax error')
        ->and(UnreadableResponse::notAnEnvelope()->getMessage())->toContain('not the wrapper')
        ->and(UnreadableResponse::versionMissing()->getMessage())->toContain('no whole-number api_version')
        ->and(UnreadableResponse::kindMissing()->getMessage())->toContain('carries no kind')
        ->and(UnreadableResponse::dataMissing()->getMessage())->toContain('carries no data');
});

it('names the kind read for and the kind that arrived', function (): void {
    $problem = UnexpectedKind::between('word', 'log');

    expect($problem->wanted())->toBe('word')
        ->and($problem->carried())->toBe('log')
        ->and($problem->getMessage())->toBe(
            'This envelope carries the kind log and was read as word. What an envelope holds is shaped by its kind, so nothing was taken from it.',
        );
});

it('describes every way a setting can be wrong', function (): void {
    expect(ConfigurationProblem::unreadableAddress('::')->getMessage())->toContain('could not be read')
        ->and(ConfigurationProblem::addressIsNotOnThisMachine('example.com')->getMessage())->toContain('example.com')
        ->and(ConfigurationProblem::unsupportedScheme('ftp')->getMessage())->toContain('ftp')
        ->and(ConfigurationProblem::addressCarriesExtras()->getMessage())->toContain('nothing else')
        ->and(ConfigurationProblem::portOutOfRange(0)->getMessage())->toContain('1 to 65535')
        ->and(ConfigurationProblem::tokenIsEmpty()->getMessage())->toContain('No run token')
        ->and(ConfigurationProblem::tokenHasHiddenCharacters()->getMessage())->toContain('Copy it again')
        ->and(ConfigurationProblem::lengthOfTimeNotPositive(0)->getMessage())->toContain('more than zero')
        ->and(ConfigurationProblem::reconnectLimitBelowZero(-2)->getMessage())->toContain('-2 was given');
});

it('gathers every failure under one type', function (): void {
    expect(ApiVersionMismatch::between(1, 2))->toBeInstanceOf(Problem::class)
        ->and(RequestFailed::from('/api/status', 500, ''))->toBeInstanceOf(Problem::class)
        ->and(StreamInterrupted::ended())->toBeInstanceOf(Problem::class)
        ->and(UnreadableResponse::notAnEnvelope())->toBeInstanceOf(Problem::class)
        ->and(UnexpectedKind::between('word', 'log'))->toBeInstanceOf(Problem::class)
        ->and(ConfigurationProblem::tokenIsEmpty())->toBeInstanceOf(Problem::class);
});

it('says nothing technical about what went wrong', function (Throwable $problem): void {
    $said = strtolower($problem->getMessage());

    foreach (['exception', 'null', 'stack', 'http', 'parse', 'invalid', 'buffer'] as $jargon) {
        expect(str_contains($said, $jargon))->toBeFalse();
    }
})->with([
    'versions disagree' => [ApiVersionMismatch::between(1, 2)],
    'the request was turned down' => [RequestFailed::from('/api/status', 500, '')],
    'the stream ended' => [StreamInterrupted::ended()],
    'the stream went quiet' => [StreamInterrupted::wentQuiet(15_000)],
    'the stream never opened' => [StreamInterrupted::neverOpened()],
    'reopening gave up' => [StreamInterrupted::gaveUpAfter(1)],
    'the answer was not a wrapper' => [UnreadableResponse::notAnEnvelope()],
    'the envelope carries another kind' => [UnexpectedKind::between('word', 'log')],
    'no token' => [ConfigurationProblem::tokenIsEmpty()],
    'the address carries extras' => [ConfigurationProblem::addressCarriesExtras()],
]);
