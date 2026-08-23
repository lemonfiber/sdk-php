<?php

declare(strict_types=1);

use Lemonfiber\Sdk\Exception\ConfigurationProblem;
use Lemonfiber\Sdk\Time\Duration;

it('splits a length of time into whole seconds and the microseconds left over', function (): void {
    $duration = Duration::ofMilliseconds(1500);

    expect($duration->milliseconds)->toBe(1500)
        ->and($duration->wholeSeconds())->toBe(1)
        ->and($duration->remainingMicroseconds())->toBe(500_000)
        ->and($duration->inSeconds())->toBe(1.5);
});

it('reads a whole number of seconds', function (): void {
    $duration = Duration::ofSeconds(5);

    expect($duration->milliseconds)->toBe(5000)
        ->and($duration->wholeSeconds())->toBe(5)
        ->and($duration->remainingMicroseconds())->toBe(0)
        ->and($duration->inSeconds())->toBe(5.0);
});

it('accepts the smallest length of time there is', function (): void {
    expect(Duration::ofMilliseconds(1)->milliseconds)->toBe(1);
});

it('refuses a length of time of no milliseconds', function (): void {
    expect(fn(): Duration => Duration::ofMilliseconds(0))
        ->toThrow(ConfigurationProblem::class, 'more than zero milliseconds. 0 was given');
});

it('refuses a length of time below zero', function (): void {
    expect(fn(): Duration => Duration::ofMilliseconds(-1))
        ->toThrow(ConfigurationProblem::class, '-1 was given');
});
