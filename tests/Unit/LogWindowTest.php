<?php

declare(strict_types=1);

use Lemonfiber\Sdk\Contract\Api;
use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Exception\UnexpectedKind;
use Lemonfiber\Sdk\Logs;
use Lemonfiber\Sdk\LogWindow;

/**
 * @return Envelope<mixed>
 */
function aLine(string $said, string $service = 'sonarr'): Envelope
{
    return new Envelope(Api::VERSION, 'log', [
        'at' => '2026-09-14T02:10:00Z',
        'line' => $said,
        'service' => $service,
        'stream' => 'stdout',
    ]);
}

it('hands back the lines it was given, in the order they arrived', function (): void {
    $window = LogWindow::of(Logs::ofService('sonarr', 3), [aLine('first'), aLine('second')]);

    $lines = $window->lines();

    expect($lines)->toHaveCount(2)
        ->and($lines[0]->data['line'])->toBe('first')
        ->and($lines[1]->data['line'])->toBe('second');
});

it('carries the service and the size it was asked for', function (): void {
    $window = LogWindow::of(Logs::ofService('qbittorrent', 50), [aLine('a line', 'qbittorrent')]);

    expect($window->service())->toBe('qbittorrent')
        ->and($window->bound())->toBe(50)
        ->and($window->count())->toBe(1);
});

it('says the view stops at the bound where the bound was filled', function (): void {
    // Every line that was asked for came back, so the edge of the view is the
    // number that was written on it, and what is behind that edge is not
    // carried on the wire and is not guessed at here.
    $window = LogWindow::of(Logs::ofService('sonarr', 2), [aLine('first'), aLine('second')]);

    expect($window->reachedTheBound())->toBeTrue();
});

it('says the bound cut nothing where fewer lines came back', function (): void {
    $window = LogWindow::of(Logs::ofService('sonarr', 2), [aLine('the only one')]);

    expect($window->reachedTheBound())->toBeFalse();
});

it('is a window with nothing in it where the service has said nothing', function (): void {
    $window = LogWindow::of(Logs::ofService('sonarr', 20), []);

    expect($window->lines())->toBe([])
        ->and($window->count())->toBe(0)
        ->and($window->reachedTheBound())->toBeFalse();
});

it('refuses a line wearing another kind', function (): void {
    $notALine = new Envelope(Api::VERSION, 'status', ['health' => 'healthy']);

    expect(fn(): LogWindow => LogWindow::of(Logs::ofService('sonarr', 2), [aLine('first'), $notALine]))
        ->toThrow(UnexpectedKind::class, 'carries the kind status and was read as log');
});
