<?php

declare(strict_types=1);

use Lemonfiber\Sdk\Client;
use Lemonfiber\Sdk\Contract\Api;
use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Events\EventFeed;
use Lemonfiber\Sdk\Exception\ConfigurationProblem;
use Lemonfiber\Sdk\Exception\NoSuchJob;
use Lemonfiber\Sdk\Exception\RequestFailed;
use Lemonfiber\Sdk\Http\ActionRequest;
use Lemonfiber\Sdk\Http\ReadRequest;
use Lemonfiber\Sdk\Http\ReleaseRequest;
use Lemonfiber\Sdk\JobStanding;
use Lemonfiber\Sdk\Logs;
use Lemonfiber\Sdk\LogWindow;
use Lemonfiber\Sdk\Repair;
use Lemonfiber\Sdk\Time\Duration;
use Saloon\Enums\Method;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

const A_RUN_TOKEN = 'a-run-token';

/**
 * @param  array<class-string, MockResponse>  $answers
 * @return array{0: Client, 1: MockClient}
 */
function clientAnswering(array $answers): array
{
    $mock = new MockClient($answers);
    $client = Client::onPort(9000, A_RUN_TOKEN);
    $client->connector()->withMockClient($mock);

    return [$client, $mock];
}

it('reads an endpoint and hands back the envelope', function (): void {
    [$client] = clientAnswering([
        ReadRequest::class => MockResponse::make('{"api_version":1,"kind":"status","data":{"health":"healthy"}}'),
    ]);

    $envelope = $client->read('/api/status');

    expect($envelope->kind)->toBe('status')
        ->and($envelope->data)->toBe(['health' => 'healthy']);
});

it('sends the token as a header and never in the address', function (): void {
    [$client, $mock] = clientAnswering([
        ReadRequest::class => MockResponse::make('{"api_version":1,"kind":"logs","data":[]}'),
    ]);

    $client->read('/api/logs', ['since' => 'yesterday', 'lines' => 20]);

    $pending = $mock->getLastPendingRequest();
    $address = (string) $pending?->getUri();

    expect($pending?->headers()->get(Api::TOKEN_HEADER))->toBe(A_RUN_TOKEN)
        ->and($address)->toBe('http://127.0.0.1:9000/api/logs?since=yesterday&lines=20')
        ->and($address)->not->toContain(A_RUN_TOKEN)
        ->and($address)->not->toContain(Api::TOKEN_HEADER);
});

it('asks for the answer as json', function (): void {
    [$client, $mock] = clientAnswering([
        ReadRequest::class => MockResponse::make('{"api_version":1,"kind":"status","data":{}}'),
    ]);

    $client->read('/api/status');

    expect($mock->getLastPendingRequest()?->headers()->get('Accept'))->toBe('application/json');
});

it('acts on an endpoint, carrying its payload as json', function (): void {
    [$client, $mock] = clientAnswering([
        ActionRequest::class => MockResponse::make('{"api_version":1,"kind":"job","data":{"id":"j1"}}'),
    ]);

    $envelope = $client->act('/api/actions/retry-import', ['service' => 'sonarr']);

    $pending = $mock->getLastPendingRequest();

    expect($envelope->kind)->toBe('job')
        ->and($pending?->body()?->all())->toBe(['service' => 'sonarr'])
        ->and($pending?->headers()->get(Api::TOKEN_HEADER))->toBe(A_RUN_TOKEN)
        ->and((string) $pending?->getUri())->toBe('http://127.0.0.1:9000/api/actions/retry-import');
});

it('names the attempt an action is part of, in a header and never in the body', function (): void {
    [$client, $mock] = clientAnswering([
        ActionRequest::class => MockResponse::make('{"api_version":1,"kind":"job","data":{"id":"j1"}}'),
    ]);

    $client->act('/api/actions/down', ['services' => ['sonarr']], 'an-attempt');

    $pending = $mock->getLastPendingRequest();

    // The body stays what the action's arguments are. lemonfiber reads an
    // action's arguments against a closed list and refuses a field it does not
    // offer, so a key put there would turn every action into a refusal.
    expect($pending?->headers()->get(Api::IDEMPOTENCY_HEADER))->toBe('an-attempt')
        ->and($pending?->body()?->all())->toBe(['services' => ['sonarr']]);
});

it('sends no attempt header where a caller named no attempt', function (): void {
    [$client, $mock] = clientAnswering([
        ActionRequest::class => MockResponse::make('{"api_version":1,"kind":"job","data":{"id":"j1"}}'),
    ]);

    $client->act('/api/actions/down');

    expect($mock->getLastPendingRequest()?->headers()->get(Api::IDEMPOTENCY_HEADER))->toBeNull();
});

it('refuses to send an action under a key that cannot travel', function (): void {
    [$client] = clientAnswering([
        ActionRequest::class => MockResponse::make('{"api_version":1,"kind":"job","data":{"id":"j1"}}'),
    ]);

    expect(fn(): Envelope => $client->act('/api/actions/down', [], "key\r\nIdempotency-Key: theirs"))
        ->toThrow(ConfigurationProblem::class, 'cannot travel in a request');
});

it('carries an attempt through a repair, which is the action it changes most with', function (): void {
    [$client, $mock] = clientAnswering([
        ActionRequest::class => MockResponse::make(
            '{"api_version":1,"kind":"job","data":{"job":"j1","action":"repair"}}',
        ),
    ]);

    $client->repair(Repair::agreedInAdvance(), 'an-attempt');

    expect($mock->getLastPendingRequest()?->headers()->get(Api::IDEMPOTENCY_HEADER))->toBe('an-attempt');
});

it('asks what could be put right, at the endpoint it never asked a caller for', function (): void {
    // The offer half. A caller spelling `/api/actions/repair` itself is a
    // caller that goes on spelling it the day lemonfiber moves it, so the
    // path is composed here and the body is a shape rather than an array.
    [$client, $mock] = clientAnswering([
        ActionRequest::class => MockResponse::make(
            '{"api_version":1,"kind":"job","data":{"job":"j1","action":"repair"}}',
            202,
        ),
    ]);

    $envelope = $client->repair(Repair::offer());

    $pending = $mock->getLastPendingRequest();

    expect($envelope->kind)->toBe('job')
        ->and((string) $pending?->getUri())->toBe('http://127.0.0.1:9000/api/actions/repair')
        ->and($pending?->body()?->all())->toBe(['confirm' => false]);
});

it('carries the yes as every other request carries what it says', function (): void {
    // The same header, the same media type and the same token as a read. An
    // action that travelled differently would be a second transport, and the
    // repair is the one where a request going astray carries out work.
    [$client, $mock] = clientAnswering([
        ActionRequest::class => MockResponse::make(
            '{"api_version":1,"kind":"job","data":{"job":"j1","action":"repair"}}',
            202,
        ),
    ]);

    $client->repair(Repair::agreedTo('a4f1c0e9', 'vpn.killswitch', 'media.permissions'));

    $pending = $mock->getLastPendingRequest();
    $address = (string) $pending?->getUri();

    expect($pending?->body()?->all())->toBe([
        'confirm' => true,
        'offer' => 'a4f1c0e9',
        'agreed' => ['vpn.killswitch', 'media.permissions'],
    ])
        ->and($pending?->headers()->get(Api::TOKEN_HEADER))->toBe(A_RUN_TOKEN)
        ->and($pending?->headers()->get('Accept'))->toBe(Api::JSON_MEDIA_TYPE)
        ->and($pending?->headers()->get('Content-Type'))->toBe(Api::JSON_MEDIA_TYPE)
        ->and($address)->not->toContain(A_RUN_TOKEN);
});

/**
 * The answer work still going and work ended both arrive as.
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
        static fn(): string => 'ended',
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

it('tells work that ended apart from work still going', function (): void {
    // Both are the `job` envelope, so only the status separates them.
    [$client] = clientAnswering([ReadRequest::class => stillNamed(200)]);

    expect(standingWord($client->whatBecameOf('k3n9v2xq')))->toBe('ended');
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

it('reports any other refusal of a name as the request having failed', function (): void {
    [$client] = clientAnswering([
        ReadRequest::class => MockResponse::make('{}', 500, ['Content-Type' => 'application/json']),
    ]);

    expect(fn(): JobStanding => $client->whatBecameOf('k3n9v2xq'))
        ->toThrow(RequestFailed::class, '/api/jobs/k3n9v2xq');
});

it('lets go of a name, and says where the work it stood for now stands', function (): void {
    // A screen has nothing to interrupt with, so the name is the handle. What
    // comes back is the same answer asking would have given.
    [$client, $mock] = clientAnswering([ReleaseRequest::class => stillNamed(200)]);

    $standing = $client->letGoOf('k3n9v2xq');

    $pending = $mock->getLastPendingRequest();

    expect(standingWord($standing))->toBe('ended')
        ->and($pending?->getMethod())->toBe(Method::DELETE)
        ->and((string) $pending?->getUri())->toBe('http://127.0.0.1:9000/api/jobs/k3n9v2xq')
        ->and($pending?->headers()->get(Api::TOKEN_HEADER))->toBe(A_RUN_TOKEN);
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

it('reads a bounded window of one service\'s lines', function (): void {
    $body = '{"api_version":1,"kind":"log","data":{"at":"2026-09-14T02:10:00Z","line":"started","service":"sonarr","stream":"stdout"}}' . "\n"
        . '{"api_version":1,"kind":"log","data":{"at":"2026-09-14T02:10:01Z","line":"listening","service":"sonarr","stream":"stderr"}}' . "\n";

    [$client, $mock] = clientAnswering([
        ReadRequest::class => MockResponse::make($body),
    ]);

    $window = $client->logs(Logs::ofService('sonarr', 2));
    $lines = $window->lines();

    expect($window)->toBeInstanceOf(LogWindow::class)
        ->and((string) $mock->getLastPendingRequest()?->getUri())
        ->toBe('http://127.0.0.1:9000/api/logs?service=sonarr&tail=2')
        ->and($window->service())->toBe('sonarr')
        ->and($window->bound())->toBe(2)
        ->and($window->count())->toBe(2)
        ->and($window->reachedTheBound())->toBeTrue()
        ->and($lines[0]->data['line'])->toBe('started')
        ->and($lines[1]->data['stream'])->toBe('stderr');
});

it('says the bound cut nothing where the service had less to say', function (): void {
    [$client] = clientAnswering([
        ReadRequest::class => MockResponse::make(
            '{"api_version":1,"kind":"log","data":{"line":"started","service":"sonarr","stream":"stdout"}}' . "\n",
        ),
    ]);

    $window = $client->logs(Logs::ofService('sonarr', 200));

    expect($window->count())->toBe(1)
        ->and($window->reachedTheBound())->toBeFalse();
});

it('reads a service that has said nothing as a window with nothing in it', function (): void {
    [$client] = clientAnswering([
        ReadRequest::class => MockResponse::make(''),
    ]);

    expect($client->logs(Logs::ofService('sonarr', 50))->lines())->toBe([]);
});

it('hands over the ceiling lemonfiber names, rather than holding one of its own', function (): void {
    $said = 'How many lines to begin with must be a number, and no more than 10000.';

    [$client] = clientAnswering([
        ReadRequest::class => MockResponse::make($said, 400),
    ]);

    $problem = null;

    try {
        $client->logs(Logs::ofService('sonarr', 20_000));
    } catch (RequestFailed $refusal) {
        $problem = $refusal;
    }

    expect($problem?->said())->toBe($said)
        ->and($problem?->status())->toBe(400)
        ->and($problem?->endpoint())->toBe(Api::LOGS_ENDPOINT);
});

it('reports an endpoint that was turned down, reading nothing from it', function (): void {
    [$client] = clientAnswering([
        ReadRequest::class => MockResponse::make('{"api_version":1,"kind":"error","data":{}}', 401),
    ]);

    expect(fn(): Envelope => $client->read('/api/status'))
        ->toThrow(RequestFailed::class, 'turned down the request for /api/status and answered 401');
});

it('reports an action that was turned down', function (): void {
    [$client] = clientAnswering([
        ActionRequest::class => MockResponse::make('{}', 500),
    ]);

    expect(fn(): Envelope => $client->act('/api/actions/repair'))
        ->toThrow(RequestFailed::class, 'answered 500');
});

it('hands the caller the sentence a read was refused with', function (): void {
    $said = 'That is not a group of checks lemonfiber knows.';

    [$client] = clientAnswering([
        ReadRequest::class => MockResponse::make($said, 400),
    ]);

    $problem = null;

    try {
        $client->read('/api/doctor', ['only' => 'nope']);
    } catch (RequestFailed $refusal) {
        $problem = $refusal;
    }

    expect($problem?->getMessage())->toBe($said)
        ->and($problem?->said())->toBe($said)
        ->and($problem?->status())->toBe(400)
        ->and($problem?->endpoint())->toBe('/api/doctor');
});

it('is built from a written out address', function (): void {
    $client = Client::at('http://127.0.0.1:9000', A_RUN_TOKEN);

    expect($client->connector()->resolveBaseUrl())->toBe('http://127.0.0.1:9000');
});

it('refuses to be built against another machine', function (): void {
    expect(fn(): Client => Client::at('http://example.com:9000', A_RUN_TOKEN))
        ->toThrow(ConfigurationProblem::class, 'points somewhere else');
});

it('is built against another machine when a certificate digest vouches for it', function (): void {
    $client = Client::pinnedAt(
        'https://192.168.1.42:9000',
        A_RUN_TOKEN,
        '86b25c676b761e9a398081373fec783c2bec970baa255370838aebb5c687841e',
    );

    expect($client->connector()->resolveBaseUrl())->toBe('https://192.168.1.42:9000');
});

it('refuses to be built against another machine with a digest it cannot use', function (): void {
    expect(fn(): Client => Client::pinnedAt('https://192.168.1.42:9000', A_RUN_TOKEN, 'not-a-digest'))
        ->toThrow(ConfigurationProblem::class, '64 hexadecimal characters');
});

it('refuses to be built without a token', function (): void {
    expect(fn(): Client => Client::onPort(9000, ''))
        ->toThrow(ConfigurationProblem::class, 'No run token was given');
});

it('waits as long as it was told between reads, or a quarter second', function (): void {
    $client = Client::onPort(9000, A_RUN_TOKEN);

    expect($client->eventSource()->wait->milliseconds)->toBe(250)
        ->and($client->eventSource(Duration::ofMilliseconds(75))->wait->milliseconds)->toBe(75);
});

it('opens a feed of live updates', function (): void {
    $client = Client::onPort(9000, A_RUN_TOKEN);

    expect($client->events(Duration::ofSeconds(15)))->toBeInstanceOf(EventFeed::class)
        ->and($client->events(Duration::ofSeconds(15), Duration::ofMilliseconds(50), 2))->toBeInstanceOf(EventFeed::class);
});
