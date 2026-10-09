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
    // The vendored contract carries envelope kinds and no endpoints,
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
    // Literals for the reason the endpoints above are literals, and each
    // written out rather than looped over a list: a loop would assert the list
    // against itself and pass whatever the list said.
    //
    // How many there are is deliberately not said. A count in a comment is a
    // number nothing computes, and the next read added is what makes it wrong.
    expect(Api::VERSION_ENDPOINT)->toBe('/api/version')
        ->and(Api::FORMS_ENDPOINT)->toBe('/api/forms')
        ->and(Api::STATUS_ENDPOINT)->toBe('/api/status')
        ->and(Api::SERVICES_ENDPOINT)->toBe('/api/services')
        ->and(Api::STORAGE_ENDPOINT)->toBe('/api/storage')
        ->and(Api::REQUESTS_ENDPOINT)->toBe('/api/requests')
        ->and(Api::HELD_ENDPOINT)->toBe('/api/held')
        ->and(Api::WATCHING_ENDPOINT)->toBe('/api/watching')
        ->and(Api::PLAYING_ENDPOINT)->toBe('/api/playing')
        ->and(Api::CONFIG_ENDPOINT)->toBe('/api/config')
        ->and(Api::QUALITY_ENDPOINT)->toBe('/api/quality')
        ->and(Api::TRACE_ENDPOINT)->toBe('/api/trace')
        ->and(Api::STUCK_ENDPOINT)->toBe('/api/stuck')
        ->and(Api::UPDATE_ENDPOINT)->toBe('/api/update')
        ->and(Api::ALERTS_ENDPOINT)->toBe('/api/alerts')
        ->and(Api::BANDWIDTH_ENDPOINT)->toBe('/api/bandwidth')
        ->and(Api::SPACE_ENDPOINT)->toBe('/api/space')
        ->and(Api::STORED_ENDPOINT)->toBe('/api/stored')
        ->and(Api::OUTBOUND_ENDPOINT)->toBe('/api/outbound')
        ->and(Api::CATALOGUE_ENDPOINT)->toBe('/api/catalogue')
        ->and(Api::PROVENANCE_ENDPOINT)->toBe('/api/provenance')
        ->and(Api::CLIENTS_ENDPOINT)->toBe('/api/clients')
        ->and(Api::CREDENTIALS_ENDPOINT)->toBe('/api/credentials')
        ->and(Api::HISTORY_ENDPOINT)->toBe('/api/history')
        ->and(Api::HOSTING_ENDPOINT)->toBe('/api/hosting')
        ->and(Api::MIGRATION_ENDPOINT)->toBe('/api/migration')
        ->and(Api::FRONT_DOOR_ENDPOINT)->toBe('/api/front-door')
        ->and(Api::NEWS_ENDPOINT)->toBe('/api/news')
        ->and(Api::EXPLAIN_ENDPOINT)->toBe('/api/explain')
        ->and(Api::BACKUPS_ENDPOINT)->toBe('/api/backups')
        ->and(Api::BUNDLE_ENDPOINT)->toBe('/api/bundle')
        ->and(Api::POSTER_ENDPOINT)->toBe('/api/held/{id}/poster')
        ->and(Api::BACKDROP_ENDPOINT)->toBe('/api/held/{id}/backdrop')
        ->and(Api::UNINSTALL_ENDPOINT)->toBe('/api/uninstall')
        ->and(Api::PLUGINS_ENDPOINT)->toBe('/api/plugins')
        ->and(Api::WIRING_ENDPOINT)->toBe('/api/wiring')
        ->and(Api::CAPABILITIES_ENDPOINT)->toBe('/api/capabilities')
        ->and(Api::KEYS_ENDPOINT)->toBe('/api/keys')
        ->and(Api::SETUP_ENDPOINT)->toBe('/api/setup');
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

it('composes the path one support bundle is asked for under', function (): void {
    // A bundle is a name under one path rather than an endpoint of its own, so
    // the join lives here for the reason the two above it do. Two names again,
    // since a join that dropped the name would answer the first by accident.
    expect(Api::bundle('t4m8'))->toBe('/api/bundle/t4m8')
        ->and(Api::bundle('w2qr'))->toBe('/api/bundle/w2qr');
});

it('composes the path one title on a shelf is read under', function (): void {
    // Two ids again, since a join that dropped the id would answer the first by
    // accident.
    expect(Api::title('4f2a9c'))->toBe('/api/held/4f2a9c')
        ->and(Api::title('81'))->toBe('/api/held/81');
});

it('keeps a title id to the one segment it names', function (): void {
    // An id carrying a separator would otherwise reach a different read.
    expect(Api::title('../keys'))->toBe('/api/held/..%2Fkeys')
        ->and(Api::title('a b?c'))->toBe('/api/held/a%20b%3Fc');
});

it('composes the paths the pictures of a title are read under', function (): void {
    expect(Api::poster('4f2a9c'))->toBe('/api/held/4f2a9c/poster')
        ->and(Api::poster('81'))->toBe('/api/held/81/poster')
        ->and(Api::backdrop('4f2a9c'))->toBe('/api/held/4f2a9c/backdrop')
        ->and(Api::backdrop('../keys'))->toBe('/api/held/..%2Fkeys/backdrop');
});

it('keeps the two media types apart', function (): void {
    // A stream read as JSON is a request that hangs until it times out.
    expect(Api::EVENT_STREAM_MEDIA_TYPE)->not->toBe(Api::JSON_MEDIA_TYPE);
});
