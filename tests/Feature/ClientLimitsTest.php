<?php

declare(strict_types=1);

use Lemonfiber\Sdk\Client;
use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Exception\RequestFailed;
use Lemonfiber\Sdk\Exception\StreamInterrupted;
use Lemonfiber\Sdk\Http\ReadRequest;
use Lemonfiber\Sdk\Http\ReleaseRequest;
use Lemonfiber\Sdk\JobStanding;
use Lemonfiber\Sdk\Time\Duration;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

/**
 * A client whose every request of this class is answered with this.
 *
 * @param  class-string  $request
 */
function aClientAnsweringEach(string $request, MockResponse $answer): Client
{
    return Client::onPort(9000, 'a-run-token', aWait())->withMockClient(new MockClient([$request => $answer]));
}

it('takes a release answered below 400 for where the work stands, and one at 400 for a refusal', function (): void {
    $named = static fn(int $status): MockResponse => MockResponse::make(
        '{"api_version":1,"kind":"job","data":{"job":"k3n9v2xq","action":"repair"}}',
        $status,
        ['Content-Type' => 'application/json'],
    );
    $standing = aClientAnsweringEach(ReleaseRequest::class, $named(399))->letGoOf('k3n9v2xq');
    $refused = aClientAnsweringEach(ReleaseRequest::class, $named(400));

    expect($standing->answering(static fn(): string => 'still going', static fn(Envelope $outcome): string => 'finished', static fn(): string => 'ended'))
        ->toBe('ended')
        ->and(static fn(): JobStanding => $refused->letGoOf('k3n9v2xq'))->toThrow(RequestFailed::class);
});

it('reopens live updates five times before it gives up, where it is told no other number', function (): void {
    $client = aClientAnsweringEach(ReadRequest::class, MockResponse::make('', 200, ['Content-Type' => 'text/event-stream']));

    expect(static function () use ($client): void {
        foreach ($client->events(Duration::ofSeconds(15))->follow() as $ignored) {
        }
    })->toThrow(StreamInterrupted::class, 'reopened 5 times');
});
