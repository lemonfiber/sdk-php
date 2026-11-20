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
 * What `$call` throws, with every frame's arguments in its trace whatever `zend.exception_ignore_args` says.
 */
function thrownWithItsArguments(Closure $call): Throwable
{
    $ignored = (string) ini_get('zend.exception_ignore_args');
    ini_set('zend.exception_ignore_args', '0');

    try {
        $call();
    } catch (Throwable $thrown) {
        return $thrown;
    } finally {
        ini_set('zend.exception_ignore_args', $ignored);
    }

    throw new LogicException('nothing was thrown');
}

/**
 * Every frame of a trace from the call under test to where it threw, printed with its arguments.
 */
function theFramesItHolds(Throwable $thrown): string
{
    $held = [];

    foreach ($thrown->getTrace() as $frame) {
        if ($frame['function'] === 'thrownWithItsArguments') {
            break;
        }

        $held[] = $frame;
    }


    return print_r($held, true);
}

/**
 * Everything a value shows where it is dumped, exported, encoded or written into a trace.
 *
 * @return array<string, string>
 */
function everyWayItIsPrinted(object $value): array
{
    $trace = thrownWithItsArguments(static function () use ($value): never {
        (static fn(object $value): never => throw new RuntimeException('printed'))($value);
    });

    return [
        'print_r' => print_r($value, true),
        'var_export' => var_export($value, true),
        'json' => (string) json_encode($value),
        'a trace' => $trace->getTraceAsString(),
        'a trace exported' => theFramesItHolds($trace),
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
    $refused = thrownWithItsArguments(static fn(): object => $door->finishByCode(WaitingCode::of(A_HANDLE_NOBODY_SEES)));

    expect($refused)->toBeInstanceOf(RequestFailed::class)
        ->and($refused->getTraceAsString())->not->toContain(A_HANDLE_NOBODY_SEES)
        ->and(theFramesItHolds($refused))->not->toContain(A_HANDLE_NOBODY_SEES)
        ->and($refused->getMessage())->not->toContain(A_HANDLE_NOBODY_SEES);
});

it('keeps the handle out of the trace of an issued answer it cannot read', function (): void {
    $door = Admission::onPort(9000, aWait())->withMockClient(new MockClient([
        CodeAskRequest::class => MockResponse::make('{"api_version":1,"kind":"sign-in-code","data":{"lapses_at":1789380000,"waiting":"' . A_HANDLE_NOBODY_SEES . '"}}'),
    ]));
    $unread = thrownWithItsArguments(static fn(): object => $door->askForACode());

    expect($unread)->toBeInstanceOf(UnreadableResponse::class)
        ->and($unread->getTraceAsString())->not->toContain(A_HANDLE_NOBODY_SEES)
        ->and(theFramesItHolds($unread))->not->toContain(A_HANDLE_NOBODY_SEES);
});
