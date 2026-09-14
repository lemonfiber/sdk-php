<?php

declare(strict_types=1);

use Lemonfiber\Sdk\Contract\Api;
use Lemonfiber\Sdk\Generated\Contract;

it('speaks the version the contract the types came from describes', function (): void {
    // Stated once. A client naming its own version number is a client that can
    // be wrong about which types it holds.
    expect(Api::VERSION)->toBe(Contract::API_VERSION);
});

it('names every endpoint it knows, because the contract names none', function (): void {
    // `contract/web-api.contract.json` carries envelope kinds and no endpoints,
    // by design — so a path is knowledge this client holds on its callers'
    // behalf. A caller spelling one is a caller that breaks silently the day
    // lemonfiber moves it, which is the whole reason these are here.
    //
    // Asserted as literals deliberately: this is the one file where the wire's
    // own spelling lives, so the test's job is to notice a change rather than
    // to restate a derivation.
    expect(Api::EVENTS_ENDPOINT)->toBe('/api/events')
        ->and(Api::LOGS_ENDPOINT)->toBe('/api/logs')
        ->and(Api::CHECKS_ENDPOINT)->toBe('/api/checks')
        ->and(Api::ACTIONS_ENDPOINT)->toBe('/api/actions')
        ->and(Api::JOBS_ENDPOINT)->toBe('/api/jobs');
});

it('names the reads it holds a path for, and holds no path it has no caller for', function (): void {
    // Literals for the reason the endpoints above are literals. Five, and each
    // written out rather than looped over a list: a loop would assert the list
    // against itself and pass whatever the list said.
    expect(Api::STATUS_ENDPOINT)->toBe('/api/status')
        ->and(Api::SERVICES_ENDPOINT)->toBe('/api/services')
        ->and(Api::STORAGE_ENDPOINT)->toBe('/api/storage')
        ->and(Api::REQUESTS_ENDPOINT)->toBe('/api/requests')
        ->and(Api::STUCK_ENDPOINT)->toBe('/api/stuck');
});

it('composes the path one action is asked for under', function (): void {
    // An action is a name under one path rather than an endpoint of its own,
    // so the join lives here and a caller never writes either half. Two names
    // rather than one: a join that dropped the name, or put it in front of the
    // path, would answer the first correctly by accident.
    expect(Api::action('repair'))->toBe('/api/actions/repair')
        ->and(Api::action('undo'))->toBe('/api/actions/undo');
});

it('composes the path one piece of running work is asked about under', function (): void {
    // A name lemonfiber answered with is only an answer if it can be redeemed,
    // and where to redeem it is this client's to know. Two names again, since
    // a join that dropped the name would answer the first one by accident.
    expect(Api::job('k3n9v2xq'))->toBe('/api/jobs/k3n9v2xq')
        ->and(Api::job('b7p1'))->toBe('/api/jobs/b7p1');
});

it('keeps the two media types apart', function (): void {
    // A stream read as JSON is a request that hangs until it times out.
    expect(Api::EVENT_STREAM_MEDIA_TYPE)->not->toBe(Api::JSON_MEDIA_TYPE);
});
