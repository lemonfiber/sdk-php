<?php

declare(strict_types=1);

use Lemonfiber\Sdk\Http\SystemHostResolver;

it('resolves a name the machine knows', function (): void {
    expect(new SystemHostResolver()->addressesFor('localhost'))->toContain('127.0.0.1');
});

it('gives nothing for a name that resolves to nothing', function (): void {
    expect(new SystemHostResolver()->addressesFor(''))->toBe([]);
});
