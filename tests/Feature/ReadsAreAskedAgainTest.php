<?php

declare(strict_types=1);

use Lemonfiber\Sdk\Client;
use Lemonfiber\Sdk\Exception\RequestFailed;
use Lemonfiber\Sdk\Generated\RestartAction;
use Lemonfiber\Sdk\Http\ActRequest;
use Lemonfiber\Sdk\Http\BaseUrl;
use Lemonfiber\Sdk\Http\LemonfiberConnector;
use Lemonfiber\Sdk\Http\ReadRequest;
use Lemonfiber\Sdk\Time\Duration;
use Lemonfiber\Sdk\Time\SystemClock;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\PendingRequest;

// A read is asked again where a moment can clear what stopped it, and an action
// never is. Each answer below is handed out once, in order, so how many were
// taken is how many times the stack was asked.

/** An envelope a read can be answered with. */
const A_STATUS = '{"api_version":1,"kind":"status","data":{"health":"healthy"}}';

/** A client answered by these, one at a time. */
function aClientAnsweredInTurn(MockClient $mock): Client
{
    return aClientOnPort(9000, 'a-run-token')->withMockClient($mock);
}

/** What one call raised, or that it raised nothing. */
function whatTheCallRaised(Closure $ask): ?Throwable
{
    try {
        $ask();
    } catch (Throwable $raised) {
        return $raised;
    }

    return null;
}

it('asks a read again where a gateway could not reach the stack, and takes the answer that came', function (int $status): void {
    $mock = new MockClient([MockResponse::make('', $status), MockResponse::make(A_STATUS)]);

    $envelope = aClientAnsweredInTurn($mock)->read('/api/status');

    expect($envelope->kind)->toBe('status');
    $mock->assertSentCount(2, ReadRequest::class);
})->with([502, 503, 504]);

it('asks a read again where nothing answered it, and takes the answer that came', function (): void {
    $mock = new MockClient([
        MockResponse::make()->throw(static fn(PendingRequest $pending): FatalRequestException => new FatalRequestException(new RuntimeException('no route to host'), $pending)),
        MockResponse::make(A_STATUS),
    ]);

    // Without a second attempt the first would have been raised as nothing
    // answering; the envelope is what only the second could hand back.
    $envelope = aClientAnsweredInTurn($mock)->read('/api/status');

    expect($envelope->kind)->toBe('status');
});

it('asks a read three times in all, then hands back the last refusal as the stack gave it', function (): void {
    $mock = new MockClient([MockResponse::make('', 503), MockResponse::make('', 503), MockResponse::make('', 503)]);

    $raised = whatTheCallRaised(static fn(): mixed => aClientAnsweredInTurn($mock)->read('/api/status'));

    expect($raised)->toBeInstanceOf(RequestFailed::class);

    if ($raised instanceof RequestFailed) {
        expect($raised->status())->toBe(503);
    }

    $mock->assertSentCount(3, ReadRequest::class);
});

it('takes the stack\'s own refusal on the first answer', function (int $status): void {
    $mock = new MockClient([MockResponse::make('{"error":"no"}', $status)]);

    $raised = whatTheCallRaised(static fn(): mixed => aClientAnsweredInTurn($mock)->read('/api/status'));

    expect($raised)->toBeInstanceOf(RequestFailed::class);

    if ($raised instanceof RequestFailed) {
        expect($raised->status())->toBe($status);
    }

    $mock->assertSentCount(1, ReadRequest::class);
})->with([400, 401, 404, 409, 500]);

it('opens live updates again where a gateway could not reach the stack', function (): void {
    $mock = new MockClient([MockResponse::make('', 503), MockResponse::make('', 503), MockResponse::make('', 503)]);

    $raised = whatTheCallRaised(static fn(): mixed => aClientAnsweredInTurn($mock)->eventSource()->open(null));

    expect($raised)->toBeInstanceOf(RequestFailed::class);
    $mock->assertSentCount(3, ReadRequest::class);
});

it('never asks an action again, whatever answered it', function (): void {
    $mock = new MockClient([MockResponse::make('', 503), MockResponse::make('{"api_version":1,"kind":"job","data":{"job":"j","action":"restart"}}', 202)]);

    $raised = whatTheCallRaised(static fn(): mixed => aClientAnsweredInTurn($mock)->act(new RestartAction()));

    expect($raised)->toBeInstanceOf(RequestFailed::class);
    $mock->assertSentCount(1, ActRequest::class);
});

it('waits a quarter of a second before asking a read again, and twice that before the third time', function (): void {
    $connector = new LemonfiberConnector(BaseUrl::onPort(9000), aWait());
    $read = ReadRequest::envelope('/api/status');

    expect($connector->retryInterval)->toBe(250)
        ->and($read->tries)->toBe(3)
        ->and($read->useExponentialBackoff)->toBeTrue()
        ->and($read->throwOnMaxTries)->toBeFalse();
});

it('pauses for what it is told to before asking a read again', function (): void {
    $connector = new LemonfiberConnector(BaseUrl::onPort(9000), aWait(), null, new SystemClock(), Duration::ofMilliseconds(40));

    expect($connector->retryInterval)->toBe(40);
});
