<?php

declare(strict_types=1);

use Lemonfiber\Sdk\Http\ChunkedReader;
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
