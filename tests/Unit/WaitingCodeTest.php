<?php

declare(strict_types=1);

use Lemonfiber\Sdk\Admission;
use Lemonfiber\Sdk\CodeIssued;
use Lemonfiber\Sdk\CodeStillWaiting;
use Lemonfiber\Sdk\Exception\RequestFailed;
use Lemonfiber\Sdk\Exception\UnreadableResponse;
use Lemonfiber\Sdk\Http\CodeAskRequest;
use Lemonfiber\Sdk\Http\CodeFinishRequest;
use Lemonfiber\Sdk\WaitingCode;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

const A_HANDLE_NOBODY_SEES = 'a-handle-that-opens-a-session';

/**
 * Everything a value shows where it is dumped, exported, encoded or written into a trace.
 *
 * @return array<string, string>
 */
function everyWayItIsPrinted(object $value): array
{
    $trace = (static fn(object $value): Throwable => new RuntimeException('printed'))($value);

    return [
        'print_r' => print_r($value, true),
        'var_export' => var_export($value, true),
        'json' => (string) json_encode($value),
        'a trace' => $trace->getTraceAsString(),
        'a trace exported' => print_r($trace->getTrace(), true),
    ];
}

it('hands the handle back to the one request that finishes the sign-in', function (): void {
    expect(WaitingCode::of(A_HANDLE_NOBODY_SEES)->reveal())->toBe(A_HANDLE_NOBODY_SEES);
});

it('says it is hidden where it is dumped', function (): void {
    expect(WaitingCode::of(A_HANDLE_NOBODY_SEES)->__debugInfo())->toBe(['handle' => '(hidden)']);
});

it('keeps the handle out of every way a value is printed, and out of anything serialised', function (object $holding): void {
    foreach (everyWayItIsPrinted($holding) as $how => $printed) {
        expect($printed)->not->toContain(A_HANDLE_NOBODY_SEES, $how);
    }

    expect(static fn(): string => serialize($holding))->toThrow(Exception::class);
})->with([
    'the handle' => [WaitingCode::of(A_HANDLE_NOBODY_SEES)],
    'a code issued' => [new CodeIssued('ABCD', 'https://plex.tv/link', 1789380000, WaitingCode::of(A_HANDLE_NOBODY_SEES))],
    'a code still waiting' => [new CodeStillWaiting(WaitingCode::of(A_HANDLE_NOBODY_SEES), 1789380000)],
    'the request that finishes it' => [new CodeFinishRequest(WaitingCode::of(A_HANDLE_NOBODY_SEES))],
]);

it('keeps the handle out of the trace of a refused finish', function (): void {
    $door = Admission::onPort(9000, aWait())->withMockClient(new MockClient([
        CodeFinishRequest::class => MockResponse::make('{"api_version":1,"kind":"error","data":{"code":"ADMIT-15","summary":"Gone.","meaning":"m","severity":"error","state":"actionable","remedies":[]}}', 401),
    ]));
    $refused = null;

    try {
        $door->finishByCode(WaitingCode::of(A_HANDLE_NOBODY_SEES));
    } catch (RequestFailed $thrown) {
        $refused = $thrown;
    }

    expect($refused)->toBeInstanceOf(RequestFailed::class)
        ->and($refused?->getTraceAsString())->not->toContain(A_HANDLE_NOBODY_SEES)
        ->and(print_r($refused?->getTrace(), true))->not->toContain(A_HANDLE_NOBODY_SEES)
        ->and($refused?->getMessage())->not->toContain(A_HANDLE_NOBODY_SEES);
});

it('keeps the handle out of the trace of an issued answer it cannot read', function (): void {
    $door = Admission::onPort(9000, aWait())->withMockClient(new MockClient([
        CodeAskRequest::class => MockResponse::make('{"api_version":1,"kind":"sign-in-code","data":{"lapses_at":1789380000,"waiting":"' . A_HANDLE_NOBODY_SEES . '"}}'),
    ]));
    $unread = null;

    try {
        $door->askForACode();
    } catch (UnreadableResponse $thrown) {
        $unread = $thrown;
    }

    expect($unread)->toBeInstanceOf(UnreadableResponse::class)
        ->and($unread?->getTraceAsString())->not->toContain(A_HANDLE_NOBODY_SEES)
        ->and(print_r($unread?->getTrace(), true))->not->toContain(A_HANDLE_NOBODY_SEES);
});
