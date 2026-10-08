<?php

declare(strict_types=1);

use Lemonfiber\Sdk\ActionRequest;
use Lemonfiber\Sdk\Tests\Support\AnAction;

it('is asked for under its own name', function (): void {
    expect(new AnAction()->endpoint())->toBe('/api/actions/stop-seeding');
});

it('carries every argument it takes, and no dry run unless rehearsed', function (): void {
    expect(new AnAction(['tv'])->payload())->toBe(['forms' => ['tv'], 'confirm' => false]);
});

it('rehearses a copy, leaving the action it came from as it was', function (): void {
    $action = new AnAction(['tv']);
    $rehearsal = $action->rehearsed();

    expect($rehearsal->payload())->toBe(['forms' => ['tv'], 'confirm' => false, ActionRequest::DRY_RUN => true])
        ->and($action->payload())->toBe(['forms' => ['tv'], 'confirm' => false])
        ->and($rehearsal)->not->toBe($action);
});
