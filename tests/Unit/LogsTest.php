<?php

declare(strict_types=1);

use Lemonfiber\Sdk\Exception\ConfigurationProblem;
use Lemonfiber\Sdk\Logs;

const A_SERVICE = 'sonarr';

it('asks one service for the last so many lines', function (): void {
    $asked = Logs::ofService(A_SERVICE, 200);

    expect($asked->parameters())->toBe(['service' => A_SERVICE, 'tail' => 200])
        ->and($asked->service())->toBe(A_SERVICE)
        ->and($asked->bound())->toBe(200);
});

it('asks to keep reading in no arrangement at all', function (): void {
    // Following is answered with a name for work that will not end rather than
    // with lines, so it is a request of its own and nothing here can spell it.
    expect(Logs::ofService(A_SERVICE, 200)->parameters())->not->toHaveKey('follow');
});

it('refuses a window over no service', function (): void {
    // Whitespace is nothing named rather than a name made of spaces.
    expect(fn(): Logs => Logs::ofService('', 200))
        ->toThrow(ConfigurationProblem::class, 'none was named')
        ->and(fn(): Logs => Logs::ofService("  \t ", 200))
        ->toThrow(ConfigurationProblem::class, 'none was named');
});

it('refuses a window of no lines, and names the number it was given', function (): void {
    expect(fn(): Logs => Logs::ofService(A_SERVICE, 0))
        ->toThrow(ConfigurationProblem::class, 'at least one line, and 0 were asked for')
        ->and(fn(): Logs => Logs::ofService(A_SERVICE, -5))
        ->toThrow(ConfigurationProblem::class, 'at least one line, and -5 were asked for');
});

it('allows a window of a single line', function (): void {
    expect(Logs::ofService(A_SERVICE, 1)->bound())->toBe(1);
});

it('holds no ceiling of its own', function (): void {
    // The most lines a read will gather is lemonfiber's number, named in its
    // own refusal. A copy of it here is a figure this client was never given.
    expect(Logs::ofService(A_SERVICE, 10_000_000)->parameters())
        ->toBe(['service' => A_SERVICE, 'tail' => 10_000_000]);
});
