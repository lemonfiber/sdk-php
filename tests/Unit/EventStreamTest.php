<?php

declare(strict_types=1);

use Lemonfiber\Sdk\Events\EventStream;
use Lemonfiber\Sdk\Events\ServerEvent;
use Lemonfiber\Sdk\Exception\StreamInterrupted;
use Lemonfiber\Sdk\Tests\Support\FakeClock;
use Lemonfiber\Sdk\Tests\Support\FakeEventSource;
use Lemonfiber\Sdk\Time\Duration;

/**
 * @param  list<list<string>>  $connections
 * @param  list<float>  $readings
 */
function streamOver(array $connections, array $readings, int $heartbeatSeconds = 1): EventStream
{
    return new EventStream(
        new FakeEventSource($connections),
        new FakeClock($readings),
        Duration::ofSeconds($heartbeatSeconds),
    );
}

/**
 * @return array{0: list<ServerEvent>, 1: ?Throwable}
 */
function drain(EventStream $stream, ?string $lastEventId = null): array
{
    $seen = [];
    $thrown = null;

    try {
        foreach ($stream->listen($lastEventId) as $event) {
            $seen[] = $event;
        }
    } catch (Throwable $caught) {
        $thrown = $caught;
    }

    return [$seen, $thrown];
}

it('hands out the events that arrive', function (): void {
    [$seen, $thrown] = drain(streamOver([["event: status\ndata: one\n\n"]], [0.0, 0.5]));

    expect($seen)->toHaveCount(1)
        ->and($seen[0]->kind)->toBe('status')
        ->and($seen[0]->data)->toBe('one')
        ->and($thrown)->toBeInstanceOf(StreamInterrupted::class);
});

it('reports a connection that runs dry', function (): void {
    [$seen, $thrown] = drain(streamOver([[]], [0.0]));

    expect($seen)->toBe([])
        ->and($thrown)->toBeInstanceOf(StreamInterrupted::class)
        ->and($thrown?->getMessage())->toContain('The connection closed');
});

it('waits out a silence no longer than the agreed sign of life', function (): void {
    [$seen, $thrown] = drain(streamOver([['', "data: one\n\n"]], [1.0, 2.0, 2.5]));

    expect($seen)->toHaveCount(1)
        ->and($thrown?->getMessage())->toContain('The connection closed');
});

it('carries on through a single missed beat', function (): void {
    // Quiet for exactly two beats, which is the edge itself: a stream that misses
    // one is far more often having a slow moment than lying dead, and ending it
    // on the first miss is what the agreed doubling exists to prevent. Sitting on
    // the boundary rather than inside it is what holds `>` apart from `>=`.
    [$seen, $thrown] = drain(streamOver([['', "data: one\n\n"]], [1.0, 3.0, 3.5]));

    expect($seen)->toHaveCount(1)
        ->and($thrown?->getMessage())->toContain('The connection closed');
});

it('reports a silence longer than twice the agreed sign of life', function (): void {
    [$seen, $thrown] = drain(streamOver([['']], [1.0, 3.001]));

    expect($seen)->toBe([])
        ->and($thrown?->getMessage())->toContain('Live updates stopped arriving');
});

it('names the agreed sign of life when it reports a silence', function (): void {
    [, $thrown] = drain(streamOver([['']], [0.0, 99.0], 15));

    expect($thrown?->getMessage())->toContain('every 15000 milliseconds');
});

it('counts a silence from the last thing that arrived, not from the start', function (): void {
    [$seen, $thrown] = drain(streamOver([["data: one\n\n", '', "data: two\n\n"]], [0.0, 5.0, 5.5, 6.0]));

    expect($seen)->toHaveCount(2)
        ->and($thrown?->getMessage())->toContain('The connection closed');
});

it('asks to resume from the event it was handed', function (): void {
    $source = new FakeEventSource([[]]);

    drain(new EventStream($source, new FakeClock([0.0]), Duration::ofSeconds(1)), '42');

    expect($source->resumedFrom())->toBe(['42']);
});
