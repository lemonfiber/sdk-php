<?php

declare(strict_types=1);

use Lemonfiber\Sdk\Contract\Api;
use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Exception\NoSuchJob;
use Lemonfiber\Sdk\Exception\RequestFailed;
use Lemonfiber\Sdk\Exception\UnreadableResponse;
use Lemonfiber\Sdk\Http\ForgetRequest;
use Lemonfiber\Sdk\Http\ReadRequest;
use Lemonfiber\Sdk\Http\ReleaseRequest;
use Lemonfiber\Sdk\JobStanding;
use Saloon\Enums\Method;
use Saloon\Http\Faking\MockResponse;

/**
 * The answer work still going arrives as.
 */
function stillNamed(int $status): MockResponse
{
    return MockResponse::make(
        '{"api_version":1,"kind":"job","data":{"job":"k3n9v2xq","action":"repair"}}',
        $status,
        ['Content-Type' => 'application/json'],
    );
}

/**
 * Which of the three standings an answer came to, as a word.
 */
function standingWord(JobStanding $standing): string
{
    return $standing->answering(
        static fn(): string => 'still going',
        static fn(Envelope $outcome): string => 'finished as ' . $outcome->kind,
        static fn(string $why): string => 'ended: ' . $why,
    );
}

/**
 * The answer work that ended before it finished arrives as.
 */
function endedAs(string $why): MockResponse
{
    return MockResponse::make(
        '{"api_version":1,"kind":"ended","data":{"job":"k3n9v2xq","action":"repair","ended":"' . $why . '"}}',
        200,
        ['Content-Type' => 'application/json'],
    );
}

it('asks what became of a name, at the path it never asked a caller for', function (): void {
    [$client, $mock] = clientAnswering([ReadRequest::class => stillNamed(202)]);

    $standing = $client->whatBecameOf('k3n9v2xq');

    $pending = $mock->getLastPendingRequest();

    expect(standingWord($standing))->toBe('still going')
        ->and($standing->job)->toBe('k3n9v2xq')
        ->and((string) $pending?->getUri())->toBe('http://127.0.0.1:9000/api/jobs/k3n9v2xq')
        ->and($pending?->headers()->get(Api::TOKEN_HEADER))->toBe(A_RUN_TOKEN)
        ->and($pending?->headers()->get('Accept'))->toBe(Api::JSON_MEDIA_TYPE);
});

it('tells work that ended apart from work still going, and says why it ended', function (): void {
    [$client] = clientAnswering([ReadRequest::class => endedAs('let-go')]);

    expect(standingWord($client->whatBecameOf('k3n9v2xq')))->toBe('ended: let-go');
});

it('refuses a name answered as though the work were over', function (): void {
    [$client] = clientAnswering([ReadRequest::class => stillNamed(200)]);

    expect(fn(): JobStanding => $client->whatBecameOf('k3n9v2xq'))
        ->toThrow(UnreadableResponse::class, 'named the work at 200');
});

it('hands over what finished work came to', function (): void {
    [$client] = clientAnswering([
        ReadRequest::class => MockResponse::make(
            '{"api_version":1,"kind":"repair","data":{"acted":false,"offered":[]}}',
            200,
            ['Content-Type' => 'application/json'],
        ),
    ]);

    expect(standingWord($client->whatBecameOf('k3n9v2xq')))->toBe('finished as repair');
});

it('says a name this run never handed out is not work that failed', function (): void {
    // Answered in prose, which is how this surface labels what it says in its
    // own words. A name goes when the run that minted it goes, so one carried
    // across a restart arrives here.
    [$client] = clientAnswering([
        ReadRequest::class => MockResponse::make(
            'No work in this run goes by that name.',
            404,
            ['Content-Type' => 'text/plain; charset=utf-8'],
        ),
    ]);

    expect(fn(): JobStanding => $client->whatBecameOf('k3n9v2xq'))
        ->toThrow(NoSuchJob::class, 'names nothing now');
});

it('says a name answered with no media type at all is not work that failed', function (): void {
    [$client] = clientAnswering([
        ReadRequest::class => MockResponse::make('No work in this run goes by that name.', 404),
    ]);

    expect(fn(): JobStanding => $client->whatBecameOf('k3n9v2xq'))->toThrow(NoSuchJob::class);
});

it('tells work that failed apart from a name nobody minted, on the same status', function (): void {
    // A problem answers with the `error` envelope at whatever status it
    // warrants, and that can be 404 too — a form nothing declares is absent
    // whichever door asked about it.
    [$client] = clientAnswering([
        ReadRequest::class => MockResponse::make(
            '{"api_version":1,"kind":"error","data":{"code":"FORM-1","summary":"Nothing here declares a form called tv.","meaning":"m","severity":"error","state":"actionable","remedies":[]}}',
            404,
            ['Content-Type' => 'application/json'],
        ),
    ]);

    expect(fn(): JobStanding => $client->whatBecameOf('k3n9v2xq'))
        ->toThrow(RequestFailed::class, 'Nothing here declares a form called tv.');
});

it('reads the first status that is a refusal as a refusal, not as where work stands', function (): void {
    [$client] = clientAnswering([
        ReadRequest::class => MockResponse::make('{}', 400, ['Content-Type' => 'application/json']),
    ]);

    expect(fn(): JobStanding => $client->whatBecameOf('k3n9v2xq'))
        ->toThrow(RequestFailed::class, 'answered 400');
});

it('reports any other refusal of a name as the request having failed', function (): void {
    [$client] = clientAnswering([
        ReadRequest::class => MockResponse::make('{}', 500, ['Content-Type' => 'application/json']),
    ]);

    expect(fn(): JobStanding => $client->whatBecameOf('k3n9v2xq'))
        ->toThrow(RequestFailed::class, '/api/jobs/k3n9v2xq');
});

it('lets go of a name, and says where the work it stood for now stands', function (): void {
    [$client, $mock] = clientAnswering([ReleaseRequest::class => endedAs('released')]);

    $standing = $client->letGoOf('k3n9v2xq');

    $pending = $mock->getLastPendingRequest();

    expect(standingWord($standing))->toBe('ended: released')
        ->and($pending?->getMethod())->toBe(Method::DELETE)
        ->and((string) $pending?->getUri())->toBe('http://127.0.0.1:9000/api/jobs/k3n9v2xq')
        ->and($pending?->headers()->get(Api::TOKEN_HEADER))->toBe(A_RUN_TOKEN);
});

it('forgets a name, leaving its work running, and says where the work stood', function (): void {
    [$client, $mock] = clientAnswering([ForgetRequest::class => stillNamed(202)]);

    $standing = $client->forget('k3n9v2xq');

    $pending = $mock->getLastPendingRequest();

    expect(standingWord($standing))->toBe('still going')
        ->and($pending?->getMethod())->toBe(Method::POST)
        ->and((string) $pending?->getUri())->toBe('http://127.0.0.1:9000/api/jobs/k3n9v2xq/forget')
        ->and($pending?->headers()->get(Api::TOKEN_HEADER))->toBe(A_RUN_TOKEN);
});

it('says a name forgotten, or another credential\'s, is no such job', function (): void {
    [$client] = clientAnswering([
        ForgetRequest::class => MockResponse::make(
            '{"api_version":1,"kind":"error","data":{"code":"ASK-7","summary":"No work in this run goes by that name.","meaning":"A job was asked about that this run did not start.","remedies":[],"severity":"error","state":"actionable"}}',
            404,
            ['Content-Type' => 'application/json'],
        ),
    ]);

    expect(fn(): JobStanding => $client->forget('k3n9v2xq'))->toThrow(NoSuchJob::class);
});

it('answers a release of work that had already finished with what it finished as', function (): void {
    [$client] = clientAnswering([
        ReleaseRequest::class => MockResponse::make(
            '{"api_version":1,"kind":"repair","data":{"acted":true,"offered":[]}}',
            200,
            ['Content-Type' => 'application/json'],
        ),
    ]);

    expect(standingWord($client->letGoOf('k3n9v2xq')))->toBe('finished as repair');
});
