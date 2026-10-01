<?php

declare(strict_types=1);

use Lemonfiber\Sdk\Client;
use Lemonfiber\Sdk\Exception\RequestFailed;
use Lemonfiber\Sdk\Exception\Unreachable;
use Lemonfiber\Sdk\Http\ActionRequest;
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
// included, and an action is sent again only under the key that makes the two
// sendings one act.

/** A wait short enough for a test to sit through. */
const A_SHORT_WAIT_MS = 600;

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
    $connector = new LemonfiberConnector(BaseUrl::fromString($peer->address()), Duration::ofMilliseconds(300), RunToken::fromString('a-run-token'));
    $connector->pausingFor(Duration::ofMilliseconds(400));

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
    // The clock stands still, so the wait is a whole second throughout and
    // only the pauses decide: 0.4 s then 0.8 s both fit, 0.6 s then 1.2 s
    // leaves room for the first alone, and 1.2 s leaves room for none.
    $peer = AnsweringListener::hangingUp();
    $connector = new LemonfiberConnector(BaseUrl::fromString($peer->address()), Duration::ofSeconds(1), RunToken::fromString('a-run-token'), new FakeClock([0.0]));
    $connector->pausingFor(Duration::ofMilliseconds($firstPause));
    $read = new ReadRequest('/api/status');
    $read->retryInterval = $readsOwn;

    [, $raised] = timed(static fn(): mixed => $connector->send($read));

    expect($raised)->toBeInstanceOf(Unreachable::class)
        ->and(howManyAsked($peer))->toBe($asked);
})->with([
    'pauses that both fit' => [400, null, 3],
    'a second pause that does not' => [600, null, 2],
    'a first pause that does not' => [1200, null, 1],
    'a read whose own first pause does not' => [1, 1200, 1],
]);

it('gives every call its own wait and its own attempts, however many calls came before it', function (): void {
    $peer = AnsweringListener::hangingUp();
    $connector = new LemonfiberConnector(BaseUrl::fromString($peer->address()), Duration::ofSeconds(1), RunToken::fromString('a-run-token'), new FakeClock([0.0]));
    $connector->pausingFor(Duration::ofMilliseconds(600));
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
    // Built at 0, the call begins at 0, and by the time the attempt would be
    // sent the clock reads past the one second the call was given.
    $connector = new LemonfiberConnector(
        BaseUrl::fromString($peer->address()),
        Duration::ofSeconds(1),
        RunToken::fromString('a-run-token'),
        new FakeClock([0.0, 0.0, 2.0]),
    );

    [, $raised] = timed(static fn(): mixed => new Client($connector)->read('/api/status'));

    expect($raised)->toBeInstanceOf(Unreachable::class)
        ->and(howManyAsked($peer))->toBe(0);
});

it('sends an action again where nothing answered, and takes the answer that came', function (): void {
    $mock = new MockClient([nothingAnswers(), MockResponse::make(A_JOB, 202)]);

    // Sent once, the call would have been raised as nothing answering; the
    // envelope is what only the second sending could hand back.
    $envelope = aClientOnPort(9000, 'a-run-token')->withMockClient($mock)->act('/api/actions/restart', [], 'one-attempt');

    expect($envelope->kind)->toBe('job');
});

it('sends an action under a key twice in all, under that key both times, then reports that nothing answered', function (): void {
    $peer = AnsweringListener::hangingUp();

    $raised = whatWasRaised(static fn(): mixed => aClientAt($peer->address(), 'a-run-token')->act('/api/actions/restart', [], 'one-attempt'));

    expect($raised)->toBeInstanceOf(Unreachable::class)
        ->and(howManyAsked($peer))->toBe(2)
        ->and(substr_count($peer->heard(), 'Idempotency-Key: one-attempt'))->toBe(2);
});

it('never sends an action under a key again where the stack answered, a refusal included', function (): void {
    $mock = new MockClient([MockResponse::make('', 503), MockResponse::make(A_JOB, 202)]);

    $raised = whatWasRaised(static fn(): mixed => aClientOnPort(9000, 'a-run-token')->withMockClient($mock)->act('/api/actions/restart', [], 'one-attempt'));

    expect($raised)->toBeInstanceOf(RequestFailed::class);
    $mock->assertSentCount(1, ActionRequest::class);
});

it('never sends an action carrying no key again, even where nothing answered', function (): void {
    $peer = AnsweringListener::hangingUp();

    $raised = whatWasRaised(static fn(): mixed => aClientAt($peer->address(), 'a-run-token')->act('/api/actions/restart'));

    expect($raised)->toBeInstanceOf(Unreachable::class)
        ->and(howManyAsked($peer))->toBe(1);
});
