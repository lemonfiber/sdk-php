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
