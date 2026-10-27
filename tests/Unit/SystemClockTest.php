<?php

declare(strict_types=1);

use Lemonfiber\Sdk\Time\SystemClock;

it('counts forward in seconds', function (): void {
    $clock = new SystemClock();

    $first = $clock->elapsedSeconds();
    $second = $clock->elapsedSeconds();

    expect($first)->toBeGreaterThan(0.0)
        ->and($second)->toBeGreaterThanOrEqual($first)
        ->and($second - $first)->toBeLessThan(1.0);
});

it('reads its counter in seconds', function (): void {
    expect(new SystemClock(static fn(): int => 1_500_000_000)->elapsedSeconds())->toBe(1.5);
});
