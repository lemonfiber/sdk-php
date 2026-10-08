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
    $request = Repair::offer()->request();

    expect($request->confirm)->toBeFalse()
        ->and($request->offer)->toBeNull()
        ->and($request->agreed)->toBe([]);
});

it('carries the yes with the offer it answers and the repairs picked out of it', function (): void {
    $request = Repair::agreedTo(AN_OFFER, 'vpn.killswitch')->request();

    expect($request->confirm)->toBeTrue()
        ->and($request->offer)->toBe(AN_OFFER)
        ->and($request->agreed)->toBe(['vpn.killswitch']);
});

it('carries every repair that was picked, in the order they were named', function (): void {
    $request = Repair::agreedTo(AN_OFFER, 'vpn.killswitch', 'media.permissions', 'disk.space')->request();

    expect($request->agreed)->toBe(['vpn.killswitch', 'media.permissions', 'disk.space']);
});

it('carries the picked repairs as a list, however they were passed', function (): void {
    // A variadic collects a named argument under its name; the yes travels as
    // the list the offer's checks are named in, not as an object.
    $named = ['also' => 'disk.space'];

    expect(Repair::agreedTo(AN_OFFER, 'vpn.killswitch', ...$named)->request()->agreed)->toBe(['vpn.killswitch', 'disk.space']);
});

it('carries a yes given before there was an offer to read', function (): void {
    // Standing consent, which the command line spells `--yes`. It is here and
    // it is spelled out, so that a call carrying it says so at the call site.
    $request = Repair::agreedInAdvance()->request();

    expect($request->confirm)->toBeTrue()
        ->and($request->offer)->toBeNull()
        ->and($request->agreed)->toBe([]);
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
    expect(Repair::offer()->request()->endpoint())->toBe('/api/actions/repair')
        ->and(Repair::agreedInAdvance()->request()->endpoint())->toBe('/api/actions/repair')
        ->and(Repair::agreedTo(AN_OFFER, 'vpn.killswitch')->request()->endpoint())->toBe('/api/actions/repair');
});
