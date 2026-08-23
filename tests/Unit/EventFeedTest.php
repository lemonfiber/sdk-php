<?php

declare(strict_types=1);

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\EnvelopeReader;
use Lemonfiber\Sdk\Events\EventFeed;
use Lemonfiber\Sdk\Events\EventStream;
use Lemonfiber\Sdk\Events\HeldValues;
use Lemonfiber\Sdk\Exception\ApiVersionMismatch;
use Lemonfiber\Sdk\Exception\ConfigurationProblem;
use Lemonfiber\Sdk\Exception\StreamInterrupted;
use Lemonfiber\Sdk\Tests\Support\FakeClock;
use Lemonfiber\Sdk\Tests\Support\FakeEventSource;
use Lemonfiber\Sdk\Time\Duration;

/**
 * @param  array<string, mixed>  $data
 */
function sseEnvelope(string $kind, array $data, ?string $id = null): string
{
    $payload = json_encode(['api_version' => 1, 'kind' => $kind, 'data' => $data], JSON_THROW_ON_ERROR);
    $line = $id === null ? '' : 'id: ' . $id . "\n";

    return $line . 'event: ' . $kind . "\n" . 'data: ' . $payload . "\n\n";
}

/**
 * @param  list<list<string>>  $connections
 * @return array{0: EventFeed, 1: FakeEventSource}
 */
function feedOver(array $connections, int $reconnectLimit): array
{
    $source = new FakeEventSource($connections);

    $feed = new EventFeed(
        new EventStream($source, new FakeClock([0.0]), Duration::ofSeconds(60)),
        new EnvelopeReader(),
        new HeldValues(),
        $reconnectLimit,
    );

    return [$feed, $source];
}

/**
 * @return array{0: list<Envelope<mixed>>, 1: ?Throwable}
 */
function collect(EventFeed $feed): array
{
    $seen = [];
    $thrown = null;

    try {
        foreach ($feed->follow() as $envelope) {
            $seen[] = $envelope;
        }
    } catch (Throwable $caught) {
        $thrown = $caught;
    }

    return [$seen, $thrown];
}

it('hands out an envelope for every event', function (): void {
    [$feed] = feedOver([[sseEnvelope('status', ['health' => 'healthy'])]], 0);

    [$seen, $thrown] = collect($feed);

    expect($seen)->toHaveCount(1)
        ->and($seen[0]->kind)->toBe('status')
        ->and($seen[0]->data)->toBe(['health' => 'healthy'])
        ->and($thrown)->toBeInstanceOf(StreamInterrupted::class);
});

it('marks everything held before a break as out of date', function (): void {
    [$feed] = feedOver([
        [sseEnvelope('status', ['health' => 'healthy'])],
        [],
    ], 1);

    [$seen] = collect($feed);

    expect($seen)->toHaveCount(1)
        ->and($feed->held()->get('status')?->isStale())->toBeTrue()
        ->and($feed->held()->get('status')?->envelope->data)->toBe(['health' => 'healthy']);
});

it('holds a value gathered after a break as current', function (): void {
    [$feed] = feedOver([
        [sseEnvelope('status', ['health' => 'healthy'])],
        [sseEnvelope('status', ['health' => 'degraded'])],
        [],
    ], 2);

    collect($feed);

    expect($feed->held()->get('status')?->isStale())->toBeTrue();
});

it('holds a value as current while the connection stands', function (): void {
    [$feed] = feedOver([[sseEnvelope('status', ['health' => 'healthy'])]], 0);

    $found = null;

    foreach ($feed->follow() as $envelope) {
        $found = $feed->held()->get($envelope->kind);

        break;
    }

    expect($found?->isStale())->toBeFalse();
});

it('reopens a broken connection up to the limit it was given', function (): void {
    [$feed] = feedOver([
        [sseEnvelope('status', ['n' => 1])],
        [sseEnvelope('status', ['n' => 2])],
        [sseEnvelope('status', ['n' => 3])],
        [],
    ], 1);

    [$seen, $thrown] = collect($feed);

    expect($seen)->toHaveCount(3)
        ->and($thrown)->toBeInstanceOf(StreamInterrupted::class)
        ->and($thrown?->getMessage())->toContain('reopened 1 times');
});

it('gives up at once when it was told to allow no reopening', function (): void {
    [$feed] = feedOver([[], [sseEnvelope('status', ['n' => 1])]], 0);

    [$seen, $thrown] = collect($feed);

    expect($seen)->toBe([])
        ->and($thrown)->toBeInstanceOf(StreamInterrupted::class);
});

it('counts no break before the first one happens', function (): void {
    [$feed] = feedOver([[], [sseEnvelope('status', ['n' => 1])], []], 1);

    [$seen] = collect($feed);

    expect($seen)->toHaveCount(1);
});

it('asks to resume from the last event it saw', function (): void {
    [$feed, $source] = feedOver([
        [sseEnvelope('status', ['n' => 1], '11')],
        [sseEnvelope('status', ['n' => 2])],
        [],
    ], 2);

    collect($feed);

    expect($source->resumedFrom())->toBe([null, '11', '11', '11']);
});

it('refuses an event speaking another version rather than handing it on', function (): void {
    $body = json_encode(['api_version' => 9, 'kind' => 'status', 'data' => []], JSON_THROW_ON_ERROR);
    [$feed] = feedOver([['event: status' . "\n" . 'data: ' . $body . "\n\n"]], 5);

    [$seen, $thrown] = collect($feed);

    expect($seen)->toBe([])
        ->and($thrown)->toBeInstanceOf(ApiVersionMismatch::class);
});

it('refuses a limit below zero', function (): void {
    expect(fn(): array => feedOver([[]], -1))
        ->toThrow(ConfigurationProblem::class, '-1 was given');
});
