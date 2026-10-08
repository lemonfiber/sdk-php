<?php

declare(strict_types=1);

use Lemonfiber\Sdk\Client;
use Lemonfiber\Sdk\Exception\RequestFailed;
use Lemonfiber\Sdk\Exception\Unreachable;
use Lemonfiber\Sdk\Generated\RestartAction;
use Lemonfiber\Sdk\Http\ActRequest;
use Lemonfiber\Sdk\Http\BaseUrl;
use Lemonfiber\Sdk\Http\LemonfiberConnector;
use Lemonfiber\Sdk\Http\ReadRequest;
use Lemonfiber\Sdk\Http\RunToken;
use Lemonfiber\Sdk\Tests\Support\AnsweringListener;
use Lemonfiber\Sdk\Tests\Support\FakeClock;
use Lemonfiber\Sdk\Time\Duration;
use Lemonfiber\Sdk\Time\SystemClock;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\PendingRequest;

// Every call waits at most the wait its client was given, every attempt at it
// included, and an action is sent once.

/** A wait short enough for a test to sit through. */
const A_SHORT_WAIT_MS = 300;

/** Room on top of the wait for starting a process and tearing a connection down. */
const LEEWAY_SECONDS = 1.5;

/** What a job envelope an action answers with looks like. */
const A_JOB = '{"api_version":1,"kind":"job","data":{"job":"j","action":"restart"}}';

/** An answer that never arrives: the connection breaks before anything comes back. */
function nothingAnswers(): MockResponse
{
    return MockResponse::make()->throw(static fn(PendingRequest $pending): FatalRequestException => new FatalRequestException(new RuntimeException('connection reset'), $pending));
}

/**
 * How long a call took, in seconds, and what it raised.
 *
 * @return array{0: float, 1: ?Throwable}
 */
function timed(Closure $ask): array
{
    $clock = new SystemClock();
    $began = $clock->elapsedSeconds();

    try {
        $ask();
        $raised = null;
    } catch (Throwable $thrown) {
        $raised = $thrown;
    }

    return [$clock->elapsedSeconds() - $began, $raised];
}

/** What one call raised, or that it raised nothing. */
function whatWasRaised(Closure $ask): ?Throwable
{
    return timed($ask)[1];
}

/** How many request heads a peer was sent. */
function howManyAsked(AnsweringListener $peer): int
{
    return substr_count($peer->heard(), 'HTTP/1.1');
}

it('gives up on a peer that never answers once the wait is over, however many attempts a read allows', function (bool $encrypted): void {
    $peer = AnsweringListener::silent($encrypted);
    $wait = Duration::ofMilliseconds(A_SHORT_WAIT_MS);
    $client = $encrypted
        ? Client::pinnedAt($peer->address(), 'a-run-token', $peer->digest, $wait)
        : Client::at($peer->address(), 'a-run-token', $wait);

    [$took, $raised] = timed(static fn(): mixed => $client->read('/api/status'));

    expect($raised)->toBeInstanceOf(Unreachable::class)
        ->and($took)->toBeGreaterThanOrEqual($wait->inSeconds() * 0.9)
        ->and($took)->toBeLessThan($wait->inSeconds() + LEEWAY_SECONDS)
        ->and(howManyAsked($peer))->toBe(1);
})->with(['plain' => [false], 'held to its certificate' => [true]]);

it('does not ask again where the pause before it would outlast the wait', function (): void {
    $peer = AnsweringListener::hangingUp();
    $connector = new LemonfiberConnector(BaseUrl::fromString($peer->address()), Duration::ofMilliseconds(300), RunToken::fromString('a-run-token'), new SystemClock(), Duration::ofMilliseconds(400));

    [, $raised] = timed(static fn(): mixed => new Client($connector)->read('/api/status'));

    expect($raised)->toBeInstanceOf(Unreachable::class)
        ->and(howManyAsked($peer))->toBe(1);
});

it('asks again while the wait has room for the pause', function (): void {
    $peer = AnsweringListener::hangingUp();

    [, $raised] = timed(static fn(): mixed => aClientAt($peer->address(), 'a-run-token')->read('/api/status'));

    expect($raised)->toBeInstanceOf(Unreachable::class)
        ->and(howManyAsked($peer))->toBe(3);
});

it('asks a read again only where the doubling pause before it leaves room in the wait', function (int $firstPause, ?int $readsOwn, int $asked): void {
    // The clock stands still, so the wait is a tenth of a second throughout
    // and only the pauses decide: 40 ms then 80 ms both fit, 60 ms then 120 ms
    // leaves room for the first alone, and 120 ms leaves room for none.
    $peer = AnsweringListener::hangingUp();
    $connector = new LemonfiberConnector(BaseUrl::fromString($peer->address()), Duration::ofMilliseconds(100), RunToken::fromString('a-run-token'), new FakeClock([0.0]), Duration::ofMilliseconds($firstPause));
    $read = ReadRequest::envelope('/api/status');
    $read->retryInterval = $readsOwn;

    [, $raised] = timed(static fn(): mixed => $connector->send($read));

    expect($raised)->toBeInstanceOf(Unreachable::class)
        ->and(howManyAsked($peer))->toBe($asked);
})->with([
    'pauses that both fit' => [40, null, 3],
    'a second pause that does not' => [60, null, 2],
    'a first pause that does not' => [120, null, 1],
    'a read whose own first pause does not' => [1, 120, 1],
]);

it('gives every call its own wait and its own attempts, however many calls came before it', function (): void {
    $peer = AnsweringListener::hangingUp();
    $connector = new LemonfiberConnector(BaseUrl::fromString($peer->address()), Duration::ofMilliseconds(100), RunToken::fromString('a-run-token'), new FakeClock([0.0]), Duration::ofMilliseconds(60));
    $client = new Client($connector);

    timed(static fn(): mixed => $client->read('/api/status'));
    timed(static fn(): mixed => $client->read('/api/status'));

    expect(howManyAsked($peer))->toBe(4);
});

it('gives up reaching live updates on a peer that never answers once the wait is over', function (): void {
    $peer = AnsweringListener::silent(true);
    $wait = Duration::ofMilliseconds(A_SHORT_WAIT_MS);

    [$took, $raised] = timed(static fn(): mixed => Client::pinnedAt($peer->address(), 'a-run-token', $peer->digest, $wait)->eventSource()->open(null)->current());

    expect($raised)->toBeInstanceOf(Unreachable::class)
        ->and($took)->toBeLessThan($wait->inSeconds() + LEEWAY_SECONDS);
});

it('makes no attempt once the call has used all of its wait', function (): void {
    $peer = AnsweringListener::plain();
    // The call begins at 0, and by the time the attempt would be sent the
    // clock reads past the one second the call was given.
    $connector = new LemonfiberConnector(
        BaseUrl::fromString($peer->address()),
        Duration::ofSeconds(1),
        RunToken::fromString('a-run-token'),
        new FakeClock([0.0, 2.0]),
    );

    [, $raised] = timed(static fn(): mixed => new Client($connector)->read('/api/status'));

    expect($raised)->toBeInstanceOf(Unreachable::class)
        ->and(howManyAsked($peer))->toBe(0);
});

it('sends an action under a key once where nothing answered, and reports that nothing answered', function (): void {
    $mock = new MockClient([nothingAnswers(), MockResponse::make(A_JOB, 202)]);

    // A second sending would have been answered with the job envelope; what is
    // raised instead is the first sending meeting nothing.
    $raised = whatWasRaised(static fn(): mixed => aClientOnPort(9000, 'a-run-token')->withMockClient($mock)->act(new RestartAction(), 'one-attempt'));

    expect($raised)->toBeInstanceOf(Unreachable::class);
});

it('sends an action under a key once, carrying the key, to a peer that hangs up', function (): void {
    $peer = AnsweringListener::hangingUp();

    $raised = whatWasRaised(static fn(): mixed => aClientAt($peer->address(), 'a-run-token')->act(new RestartAction(), 'one-attempt'));

    expect($raised)->toBeInstanceOf(Unreachable::class)
        ->and(howManyAsked($peer))->toBe(1)
        ->and(substr_count($peer->heard(), 'Idempotency-Key: one-attempt'))->toBe(1);
});

it('never sends an action under a key again where the stack answered, a refusal included', function (): void {
    $mock = new MockClient([MockResponse::make('', 503), MockResponse::make(A_JOB, 202)]);

    $raised = whatWasRaised(static fn(): mixed => aClientOnPort(9000, 'a-run-token')->withMockClient($mock)->act(new RestartAction(), 'one-attempt'));

    expect($raised)->toBeInstanceOf(RequestFailed::class);
    $mock->assertSentCount(1, ActRequest::class);
});

it('never sends an action carrying no key again, even where nothing answered', function (): void {
    $peer = AnsweringListener::hangingUp();

    $raised = whatWasRaised(static fn(): mixed => aClientAt($peer->address(), 'a-run-token')->act(new RestartAction()));

    expect($raised)->toBeInstanceOf(Unreachable::class)
        ->and(howManyAsked($peer))->toBe(1);
});
