<?php

declare(strict_types=1);

use Lemonfiber\Sdk\Exception\StreamInterrupted;
use Lemonfiber\Sdk\Http\ChunkedReader;
use Lemonfiber\Sdk\Tests\Support\AStreamThatBreaks;
use Lemonfiber\Sdk\Time\Duration;

/**
 * @return array{0: resource, 1: resource}
 */
function socketPair(): array
{
    $pair = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, 0);

    expect($pair)->toBeArray();

    /** @var array{0: resource, 1: resource} $pair */
    return $pair;
}

it('hands out what arrives and stops when the far end closes', function (): void {
    [$near, $far] = socketPair();

    fwrite($far, 'hello');
    fclose($far);

    $chunks = iterator_to_array(ChunkedReader::from($near, Duration::ofMilliseconds(50)), false);

    expect(implode('', $chunks))->toBe('hello')
        ->and(is_resource($near))->toBeFalse();
});

it('gives nothing back when a wait ends empty-handed', function (): void {
    [$near, $far] = socketPair();

    $reader = ChunkedReader::from($near, Duration::ofMilliseconds(20));

    $first = null;

    foreach ($reader as $chunk) {
        $first = $chunk;

        break;
    }

    expect($first)->toBe('')
        ->and(stream_get_meta_data($near)['timed_out'])->toBeTrue();

    unset($reader);

    expect(is_resource($near))->toBeFalse();

    fclose($far);
});

it('raises a read the connection broke under as the stream having been interrupted, and lets go of it', function (): void {
    AStreamThatBreaks::register();
    $escaped = [];
    set_error_handler(static function (int $level, string $said) use (&$escaped): bool {
        $escaped[] = $said;

        return true;
    });

    try {
        $handle = fopen(sprintf('%s://somewhere', AStreamThatBreaks::SCHEME), 'r');

        expect($handle)->toBeResource();

        /** @var resource $handle */
        $reader = ChunkedReader::from($handle, Duration::ofMilliseconds(20));
        error_clear_last();

        // The warning is the break, and it is raised as one rather than left
        // for whatever handles warnings to turn into something else.
        expect(static fn(): mixed => $reader->current())
            ->toThrow(StreamInterrupted::class, 'Live updates were cut short.')
            ->and(is_resource($handle))->toBeFalse()
            ->and($escaped)->toBe([])
            ->and(error_get_last())->toBeNull();
    } finally {
        restore_error_handler();
        AStreamThatBreaks::unregister();
    }
});

it('puts back whatever was handling warnings before a read', function (): void {
    [$near, $far] = socketPair();
    fwrite($far, 'hello');
    fclose($far);

    $before = static fn(): bool => false;
    set_error_handler($before);

    try {
        iterator_to_array(ChunkedReader::from($near, Duration::ofMilliseconds(20)), false);
        $inPlace = set_error_handler(null);
        restore_error_handler();

        expect($inPlace)->toBe($before);
    } finally {
        restore_error_handler();
    }
});
