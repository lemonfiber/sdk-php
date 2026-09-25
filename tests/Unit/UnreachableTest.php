<?php

declare(strict_types=1);

use Lemonfiber\Sdk\Exception\Problem;
use Lemonfiber\Sdk\Exception\Unreachable;

it('names the endpoint nothing answered for', function (): void {
    $problem = Unreachable::whenAsking('/api/clients', 'Connection refused');

    expect($problem)->toBeInstanceOf(Problem::class)
        ->and($problem->endpoint())->toBe('/api/clients')
        ->and($problem->getMessage())->toBe(
            'No answer came back to the request for /api/clients. lemonfiber may be stopped or asleep, or out of reach from here. Nothing was read from it.',
        );
});

it('keeps what the connection reported, in its own words', function (): void {
    expect(Unreachable::whenAsking('/api/status', 'Connection refused')->reason())->toBe('Connection refused');
});

it('cuts every address in the report back to where it points', function (string $reported, string $kept): void {
    expect(Unreachable::whenAsking('/api/status', $reported)->reason())->toBe($kept);
})->with([
    'sign-in details' => [
        'failed for http://someone:secret@127.0.0.1:9000/api/status',
        'failed for http://127.0.0.1:9000/api/status',
    ],
    'a query and a fragment' => [
        'failed for https://127.0.0.1:9000/api/logs?service=sonarr&lines=20#end',
        'failed for https://127.0.0.1:9000/api/logs',
    ],
    'no port' => [
        'fopen(http://localhost/api/events): Failed to open stream',
        'fopen(http://localhost/api/events): Failed to open stream',
    ],
    'no path' => [
        'nothing at http://127.0.0.1:9000; giving up',
        'nothing at http://127.0.0.1:9000; giving up',
    ],
    'more than one address' => [
        'error 7 (see https://someone@127.0.0.1/help?x=1) for http://127.0.0.1:1/api/status?y=2',
        'error 7 (see https://127.0.0.1/help) for http://127.0.0.1:1/api/status',
    ],
]);

it('withholds an address it cannot take apart, rather than keeping it whole', function (string $reported): void {
    expect(Unreachable::whenAsking('/api/status', $reported)->reason())->toBe('failed for [an address]');
})->with([
    'no host' => ['failed for file:///etc/secret?token=abc'],
    'unreadable' => ['failed for http://someone:secret@:80?token=abc'],
]);
