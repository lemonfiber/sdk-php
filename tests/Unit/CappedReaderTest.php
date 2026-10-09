<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\Utils;
use Lemonfiber\Sdk\Http\CappedReader;

it('reads a body no further than one byte past the most it may be, and closes it', function (): void {
    $stream = Utils::streamFor(str_repeat('x', 20));

    expect(CappedReader::upTo($stream, 10))->toBe(str_repeat('x', 11))
        ->and($stream->isReadable())->toBeFalse();
});

it('reads the whole of a body no longer than the most it may be', function (int $length): void {
    expect(CappedReader::upTo(Utils::streamFor(str_repeat('x', $length)), 10))->toBe(str_repeat('x', $length));
})->with(['empty' => [0], 'shorter' => [4], 'exactly the most' => [10], 'one past' => [11]]);

it('gathers a body that arrives a little at a time', function (): void {
    $stream = Utils::streamFor((function (): Generator {
        yield 'abc';
        yield 'def';
        yield 'ghi';
    })());

    expect(CappedReader::upTo($stream, 6))->toBe('abcdefg');
});
