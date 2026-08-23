<?php

declare(strict_types=1);

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Events\Freshness;
use Lemonfiber\Sdk\Events\HeldValues;

it('holds nothing to begin with', function (): void {
    $held = new HeldValues();

    expect($held->get('status'))->toBeNull()
        ->and($held->all())->toBe([]);
});

it('holds the last value seen for a kind', function (): void {
    $held = new HeldValues();
    $held->remember(new Envelope(1, 'status', ['health' => 'healthy']));
    $held->remember(new Envelope(1, 'status', ['health' => 'degraded']));

    $value = $held->get('status');

    expect($value?->envelope->data)->toBe(['health' => 'degraded'])
        ->and($value?->freshness)->toBe(Freshness::Current)
        ->and($value?->isStale())->toBeFalse();
});

it('marks everything held before a break as out of date', function (): void {
    $held = new HeldValues();
    $held->remember(new Envelope(1, 'status', ['health' => 'healthy']));
    $held->remember(new Envelope(1, 'storage', ['free' => 10]));

    $held->markGapped();

    expect($held->get('status')?->isStale())->toBeTrue()
        ->and($held->get('storage')?->isStale())->toBeTrue()
        ->and($held->get('status')?->envelope->data)->toBe(['health' => 'healthy'])
        ->and($held->all())->toHaveCount(2);
});

it('holds a value gathered after a break as current again', function (): void {
    $held = new HeldValues();
    $held->remember(new Envelope(1, 'status', ['health' => 'healthy']));
    $held->markGapped();
    $held->remember(new Envelope(1, 'status', ['health' => 'healthy']));

    expect($held->get('status')?->isStale())->toBeFalse();
});
