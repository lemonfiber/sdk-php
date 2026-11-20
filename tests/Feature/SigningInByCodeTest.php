<?php

declare(strict_types=1);

use Lemonfiber\Sdk\Admission;
use Lemonfiber\Sdk\Admitted;
use Lemonfiber\Sdk\CodeIssued;
use Lemonfiber\Sdk\CodeStillWaiting;
use Lemonfiber\Sdk\Exception\PasswordWasRefused;
use Lemonfiber\Sdk\Exception\RequestFailed;
use Lemonfiber\Sdk\Exception\TooManyAttempts;
use Lemonfiber\Sdk\Exception\UnexpectedKind;
use Lemonfiber\Sdk\Exception\UnreadableResponse;
use Lemonfiber\Sdk\Generated\RefusalCode;
use Lemonfiber\Sdk\Http\CodeAskRequest;
use Lemonfiber\Sdk\Http\CodeFinishRequest;
use Lemonfiber\Sdk\WaitingCode;
use Saloon\Contracts\Body\BodyRepository;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

const A_HANDLE = 'cGxlYXNlLWRvLW5vdC1sb2ctbWU';

/**
 * A code door that answers both questions with what a test says, on this machine's own port.
 *
 * @return array{0: Admission, 1: MockClient}
 */
function aCodeDoorAnswering(MockResponse $answer): array
{
    $mock = new MockClient([CodeAskRequest::class => $answer, CodeFinishRequest::class => $answer]);

    return [Admission::onPort(9000, aWait())->withMockClient($mock), $mock];
}

/** An answer of the `sign-in-code` kind carrying the fields given. */
function aSignInCode(string $data): MockResponse
{
    return MockResponse::make(sprintf('{"api_version":1,"kind":"sign-in-code","data":{%s}}', $data));
}

/**
 * A refusal under the code and status given.
 *
 * @param array<string, string> $headers
 */
function aCodeRefusal(string $code, int $status, array $headers = []): MockResponse
{
    return MockResponse::make(sprintf(
        '{"api_version":1,"kind":"error","data":{"code":"%s","summary":"Refused.","meaning":"m","severity":"error","state":"actionable","remedies":[]}}',
        $code,
    ), $status, $headers);
}

/** What a request carried in its body, as it was sent. */
function whatWasSentIn(MockClient $mock): mixed
{
    $body = $mock->getLastPendingRequest()?->body();

    return $body instanceof BodyRepository ? $body->all() : null;
}

/** What asking for a code or finishing one was refused with, or nothing where nothing refused it. */
function whatTheCodeMet(callable $asked): ?Throwable
{
    try {
        $asked();
    } catch (Throwable $refused) {
        return $refused;
    }

    return null;
}

it('asks for a code with an empty object, in JSON, at its own endpoint', function (): void {
    [$door, $mock] = aCodeDoorAnswering(aSignInCode('"code":"ABCD","approve_at":"https://plex.tv/link","lapses_at":1789380000,"waiting":"' . A_HANDLE . '"'));

    $door->askForACode();

    $pending = $mock->getLastPendingRequest();

    expect((string) $pending?->getUri())->toBe('http://127.0.0.1:9000' . Admission::CODE_ENDPOINT)
        ->and(Admission::CODE_ENDPOINT)->toBe('/api/session/code')
        ->and(whatWasSentIn($mock))->toBe('{}')
        ->and($pending?->headers()->get('Content-Type'))->toBe('application/json');
});

it('reads the code, where it is approved, when it lapses and the handle that finishes it', function (): void {
    [$door] = aCodeDoorAnswering(aSignInCode('"code":"ABCD","approve_at":"https://plex.tv/link","lapses_at":1789380000,"waiting":"' . A_HANDLE . '"'));

    $issued = $door->askForACode();

    expect([$issued->code, $issued->approveAt, $issued->lapsesAtEpochSeconds, $issued->waiting->reveal()])
        ->toBe(['ABCD', 'https://plex.tv/link', 1789380000, A_HANDLE]);
});

it('does not read an ask answered without the code or the place to approve it as a code issued', function (string $data): void {
    [$door] = aCodeDoorAnswering(aSignInCode($data));

    expect(whatTheCodeMet(fn(): CodeIssued => $door->askForACode()))->toEqual(UnreadableResponse::codeUnissued());
})->with([
    'no code' => ['"approve_at":"https://plex.tv/link","lapses_at":1789380000,"waiting":"h"'],
    'no place to approve it' => ['"code":"ABCD","lapses_at":1789380000,"waiting":"h"'],
    'a code that is not text' => ['"code":7,"approve_at":"https://plex.tv/link","lapses_at":1789380000,"waiting":"h"'],
    'a place that is not text' => ['"code":"ABCD","approve_at":null,"lapses_at":1789380000,"waiting":"h"'],
]);

it('finishes with the handle in the body, never in the address', function (): void {
    [$door, $mock] = aCodeDoorAnswering(aSignInCode('"lapses_at":1789380000,"waiting":"' . A_HANDLE . '"'));

    $door->finishByCode(WaitingCode::of(A_HANDLE));

    $pending = $mock->getLastPendingRequest();

    expect((string) $pending?->getUri())->toBe('http://127.0.0.1:9000' . Admission::CODE_ENDPOINT)
        ->and(whatWasSentIn($mock))->toBe('{"waiting":"' . A_HANDLE . '"}')
        ->and($pending?->headers()->get('Content-Type'))->toBe('application/json');
});

it('says a code the member has not approved yet still waits, and when it lapses', function (): void {
    [$door] = aCodeDoorAnswering(aSignInCode('"lapses_at":1789380000,"waiting":"' . A_HANDLE . '"'));

    $waiting = $door->finishByCode(WaitingCode::of(A_HANDLE));

    expect($waiting)->toBeInstanceOf(CodeStillWaiting::class)
        ->and($waiting instanceof CodeStillWaiting ? [$waiting->waiting->reveal(), $waiting->lapsesAtEpochSeconds] : null)->toBe([A_HANDLE, 1789380000]);
});

it('comes away with the member\'s session once the code was approved', function (): void {
    [$door] = aCodeDoorAnswering(MockResponse::make('{"api_version":1,"kind":"admission","data":{"token":"a-session","until":1789380000,"member":"robin"}}'));

    expect($door->finishByCode(WaitingCode::of(A_HANDLE)))->toEqual(Admitted::of('a-session', 1789380000, 'robin'));
});

it('does not read any other kind as a code or a session', function (): void {
    [$door] = aCodeDoorAnswering(MockResponse::make('{"api_version":1,"kind":"job","data":{}}'));

    expect(whatTheCodeMet(fn(): mixed => $door->finishByCode(WaitingCode::of(A_HANDLE))))->toBeInstanceOf(UnexpectedKind::class)
        ->and(whatTheCodeMet(fn(): mixed => $door->askForACode()))->toBeInstanceOf(UnexpectedKind::class);
});

it('reads a handle the door no longer waits on by its code, never as a wrong password', function (): void {
    [$door] = aCodeDoorAnswering(aCodeRefusal('ADMIT-15', 401));

    $met = whatTheCodeMet(fn(): mixed => $door->finishByCode(WaitingCode::of(A_HANDLE)));

    expect($met)->toBeInstanceOf(RequestFailed::class)
        ->and($met)->not->toBeInstanceOf(PasswordWasRefused::class)
        ->and($met instanceof RequestFailed ? $met->code() : null)->toBe(RefusalCode::CodeNotWaiting)
        ->and($met instanceof RequestFailed ? $met->status() : null)->toBe(401)
        ->and($met instanceof RequestFailed ? $met->endpoint() : null)->toBe(Admission::CODE_ENDPOINT);
});

it('reads a household that signs in with a password by its code', function (): void {
    [$door] = aCodeDoorAnswering(aCodeRefusal('ADMIT-17', 400));

    $met = whatTheCodeMet(fn(): CodeIssued => $door->askForACode());

    expect($met instanceof RequestFailed ? $met->code() : null)->toBe(RefusalCode::SignsInByPassword);
});

it('tells too many codes asked for apart, with how long to wait', function (): void {
    [$door] = aCodeDoorAnswering(aCodeRefusal('ADMIT-16', 429, ['Retry-After' => '45']));

    $met = whatTheCodeMet(fn(): CodeIssued => $door->askForACode());

    expect($met)->toBeInstanceOf(TooManyAttempts::class)
        ->and($met instanceof TooManyAttempts ? $met->seconds() : null)->toBe(45);
});
