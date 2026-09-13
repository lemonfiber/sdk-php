<?php

declare(strict_types=1);

use Lemonfiber\Sdk\Admission;
use Lemonfiber\Sdk\Contract\Api;
use Lemonfiber\Sdk\Exception\ConfigurationProblem;
use Lemonfiber\Sdk\Exception\PasswordWasRefused;
use Lemonfiber\Sdk\Exception\RequestFailed;
use Lemonfiber\Sdk\Exception\TooManyAttempts;
use Lemonfiber\Sdk\Http\AdmissionRequest;
use Saloon\Contracts\Body\BodyRepository;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

const A_PASSWORD = 'the-operators-password';

const A_PINNED_ADDRESS = 'https://192.168.1.42';

const A_CERTIFICATE_DIGEST = 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa';

/**
 * A door that answers with what a test says, on this machine's own port.
 *
 * @return array{0: Admission, 1: MockClient}
 */
function doorAnswering(MockResponse $answer): array
{
    $mock = new MockClient([AdmissionRequest::class => $answer]);
    $door = Admission::onPort(9000);
    $door->connector()->withMockClient($mock);

    return [$door, $mock];
}

/**
 * The sentence a door's refusal carries.
 *
 * A named function rather than an accumulating array, so each call answers
 * about one door and the analyser can see there is always a string — a list
 * built in a loop is a list whose second entry might not be there.
 */
function whatWasSaidBy(Admission $door): string
{
    try {
        $door->open(A_PASSWORD);
    } catch (PasswordWasRefused|TooManyAttempts $problem) {
        return $problem->getMessage();
    }

    return 'nothing was raised at all';
}

/** The answer a machine gives somebody who got the password right. */
function admits(): MockResponse
{
    return MockResponse::make(
        '{"api_version":1,"kind":"admission","data":{"token":"a-session","until":"2026-09-14T10:00:00Z"}}',
    );
}

it('exchanges a password for a session', function (): void {
    [$door] = doorAnswering(admits());

    $admitted = $door->open(A_PASSWORD);

    expect($admitted->token)->toBe('a-session')
        ->and($admitted->until)->toBe('2026-09-14T10:00:00Z');
});

it('sends the password in the body, to the one endpoint, and never in the address', function (): void {
    // A password in a query string is written to every proxy log between here
    // and the machine. The endpoint is the client's rather than a caller's for
    // the same reason: somewhere to send a password is not a caller's choice.
    [$door, $mock] = doorAnswering(admits());

    $door->open(A_PASSWORD);

    $pending = $mock->getLastPendingRequest();
    $address = (string) $pending?->getUri();
    $body = $pending?->body();

    expect($address)->toBe('http://127.0.0.1:9000' . Admission::ENDPOINT)
        ->and($address)->not->toContain(A_PASSWORD)
        ->and($body instanceof BodyRepository ? $body->all() : [])
        ->toBe(['password' => A_PASSWORD]);
});

it('carries no token, because this is the door that opens without one', function (): void {
    // The one route on the surface that answers a request carrying no token —
    // a caller with a password and nothing else is exactly who it is for. An
    // empty header is not the same thing: it is a token that fails comparison.
    [$door, $mock] = doorAnswering(admits());

    $door->open(A_PASSWORD);

    expect($mock->getLastPendingRequest()?->headers()->get(Api::TOKEN_HEADER))->toBeNull();
});

it('tells a wrong password apart from every other refusal', function (): void {
    // The surface answers 401 here and 403 everywhere else, and the difference
    // is the reason to have two: 403 means nothing you could send would help,
    // which is true of a missing token and false of a wrong password. A client
    // that flattened them would leave a caller unable to tell whether offering
    // a login is worth anything.
    [$door] = doorAnswering(MockResponse::make('{"error":"nope"}', 401));

    expect(fn(): mixed => $door->open(A_PASSWORD))->toThrow(PasswordWasRefused::class);
});

it('tells a door that has stopped listening apart from a wrong password', function (): void {
    // The opposite remedy: a wrong password is answered by trying another, and
    // this is answered by waiting — and by not trying another, since another
    // attempt is what extends the wait.
    [$door] = doorAnswering(MockResponse::make('{"error":"too many"}', 429, ['Retry-After' => '45']));

    try {
        $door->open(A_PASSWORD);
    } catch (TooManyAttempts $waiting) {
        expect($waiting->seconds())->toBe(45);

        return;
    }

    expect(false)->toBeTrue('The door answered 429 and nothing was raised.');
});

it('declines to guess a wait the answer did not name', function (): void {
    // A made-up countdown is worse than none: it ends while the door is still
    // shut, and the operator tries again at the one moment that extends it.
    [$door] = doorAnswering(MockResponse::make('{"error":"too many"}', 429));

    try {
        $door->open(A_PASSWORD);
    } catch (TooManyAttempts $waiting) {
        expect($waiting->seconds())->toBeNull();

        return;
    }

    expect(false)->toBeTrue('The door answered 429 and nothing was raised.');
});

it('says what happened, so a refusal is readable where it is caught', function (): void {
    // The constructor is what carries the sentence, and a refusal that reached
    // a log empty would be one nobody could act on. Asserted as the shape of
    // the sentence rather than word for word: pinning the wording would make
    // every improvement to it a failing test.
    [$refused] = doorAnswering(MockResponse::make('{"error":"nope"}', 401));
    [$waiting] = doorAnswering(MockResponse::make('{"error":"too many"}', 429, ['Retry-After' => '45']));
    [$silent] = doorAnswering(MockResponse::make('{"error":"too many"}', 429));

    foreach ([$refused, $waiting, $silent] as $door) {
        expect(whatWasSaidBy($door))->toContain('Nothing was opened');
    }

    expect(whatWasSaidBy($waiting))->toContain('45');
});

it('declines to guess a wait it cannot read as a count', function (): void {
    // `Retry-After` may carry an HTTP-date rather than seconds. Reading one
    // wrong is a countdown that ends while the door is still shut, so a header
    // this cannot read as a count takes the same road as an absent one.
    [$door] = doorAnswering(
        MockResponse::make('{"error":"too many"}', 429, ['Retry-After' => 'Sun, 14 Sep 2026 10:00:00 GMT']),
    );

    try {
        $door->open(A_PASSWORD);
    } catch (TooManyAttempts $waiting) {
        expect($waiting->seconds())->toBeNull();

        return;
    }

    expect(false)->toBeTrue('The door answered 429 and nothing was raised.');
});

it('reports any other refusal as the request having failed', function (): void {
    [$door] = doorAnswering(MockResponse::make('{"error":"gone"}', 500));

    expect(fn(): mixed => $door->open(A_PASSWORD))->toThrow(RequestFailed::class);
});

it('holds a stack reached over the network to its certificate', function (): void {
    // The one request carrying the operator's password is the last one that
    // should reach a machine whose identity nothing established, so there is no
    // unpinned counterpart to this.
    $door = Admission::at(A_PINNED_ADDRESS, A_CERTIFICATE_DIGEST);

    expect($door->connector()->resolveBaseUrl())->toBe(A_PINNED_ADDRESS);
});

it('refuses a pinned address that is not encrypted', function (): void {
    expect(fn(): mixed => Admission::at('http://192.168.1.42', A_CERTIFICATE_DIGEST))
        ->toThrow(ConfigurationProblem::class);
});
