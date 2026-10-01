<?php

declare(strict_types=1);

use Lemonfiber\Sdk\Tests\Support\FakeClock;
use Lemonfiber\Sdk\Time\Deadline;
use Lemonfiber\Sdk\Time\Duration;

it('counts down from the moment it began', function (): void {
    $deadline = Deadline::after(Duration::ofSeconds(10), new FakeClock([100.0, 103.5]));

    expect($deadline->secondsLeft())->toBe(6.5);
});

it('leaves nothing once the wait is over, rather than a time already past', function (): void {
    $deadline = Deadline::after(Duration::ofSeconds(1), new FakeClock([0.0, 4.0]));

    expect($deadline->secondsLeft())->toBe(0.0);
});

it('has room for a pause only where some of the wait would be left after it', function (float $pause, bool $room): void {
    $deadline = Deadline::after(Duration::ofSeconds(2), new FakeClock([0.0, 1.0]));

    expect($deadline->hasRoomFor($pause))->toBe($room);
})->with([
    'a pause shorter than what is left' => [0.5, true],
    'a pause exactly as long as what is left' => [1.0, false],
    'a pause longer than what is left' => [1.5, false],
]);
