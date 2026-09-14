<?php

declare(strict_types=1);

use Lemonfiber\Sdk\Exception\ConfigurationProblem;
use Lemonfiber\Sdk\Repair;

const AN_OFFER = 'a4f1c0e9';

it('asks what could be put right, and carries none of it out', function (): void {
    // The unconfirmed half of the one request. What it answers with is the
    // account somebody reads before deciding anything, and a client that sent
    // the yes along with the question would be carrying repairs out at the
    // moment it asked what they were.
    expect(Repair::offer()->arguments())->toBe(['confirm' => false]);
});

it('carries the yes with the offer it answers and the repairs picked out of it', function (): void {
    $asked = Repair::agreedTo(AN_OFFER, 'vpn.killswitch');

    expect($asked->arguments())->toBe([
        'confirm' => true,
        'offer' => AN_OFFER,
        'agreed' => ['vpn.killswitch'],
    ]);
});

it('carries every repair that was picked, in the order they were named', function (): void {
    $asked = Repair::agreedTo(AN_OFFER, 'vpn.killswitch', 'media.permissions', 'disk.space');

    expect($asked->arguments())->toBe([
        'confirm' => true,
        'offer' => AN_OFFER,
        'agreed' => ['vpn.killswitch', 'media.permissions', 'disk.space'],
    ]);
});

it('carries a yes given before there was an offer to read', function (): void {
    // Standing consent, which the command line spells `--yes`. It is here and
    // it is spelled out, so that a call carrying it says so at the call site.
    expect(Repair::agreedInAdvance()->arguments())->toBe(['confirm' => true]);
});

it('refuses an agreement that names no offer', function (): void {
    // A yes carrying no offer is a yes to whatever stands when it lands, which
    // is the arrangement the two-step exists to prevent. Whitespace is nothing
    // named rather than a name made of spaces.
    expect(fn(): Repair => Repair::agreedTo('', 'vpn.killswitch'))
        ->toThrow(ConfigurationProblem::class, 'names none')
        ->and(fn(): Repair => Repair::agreedTo("  \t ", 'vpn.killswitch'))
        ->toThrow(ConfigurationProblem::class, 'names none');
});

it('names the one place a repair is asked for', function (): void {
    expect(Repair::offer()->endpoint())->toBe('/api/actions/repair')
        ->and(Repair::agreedInAdvance()->endpoint())->toBe('/api/actions/repair')
        ->and(Repair::agreedTo(AN_OFFER, 'vpn.killswitch')->endpoint())->toBe('/api/actions/repair');
});
