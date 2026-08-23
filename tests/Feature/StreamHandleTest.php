<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\Utils;
use Lemonfiber\Sdk\Exception\StreamInterrupted;
use Lemonfiber\Sdk\Http\StreamHandle;

it('hands back the handle underneath a body', function (): void {
    $handle = StreamHandle::beneath(Utils::streamFor('hello'));

    expect(is_resource($handle))->toBeTrue()
        ->and(stream_get_contents($handle, -1, 0))->toBe('hello');

    fclose($handle);
});

it('reports a body with nothing underneath it', function (): void {
    $stream = Utils::streamFor('hello');
    $stream->detach();

    expect(fn(): mixed => StreamHandle::beneath($stream))
        ->toThrow(StreamInterrupted::class, 'handed back nothing to read from');
});
