<?php

declare(strict_types=1);

use Lemonfiber\Sdk\Scripts\ShapePlan;

/**
 * A kind's schema whose payload refers to `$payload` and which carries `$defs`.
 *
 * @param  array<string, mixed>  $defs
 * @return array<string, mixed>
 */
function kindCarrying(string $payload, array $defs): array
{
    return [
        '$defs' => $defs,
        'properties' => ['data' => ['$ref' => '#/$defs/' . $payload]],
    ];
}

/** @return array<string, mixed> */
function refTo(string $name): array
{
    return ['$ref' => '#/$defs/' . $name];
}

it('names a shape one kind reaches on that kind', function (): void {
    $plan = new ShapePlan(['WordEnvelope' => kindCarrying('Word', ['Word' => ['type' => 'string']])], []);

    expect($plan->owner('Word'))->toBe('WordEnvelope')
        ->and($plan->namedOn('WordEnvelope'))->toBe(['Word' => ['type' => 'string']])
        ->and($plan->namedOn(ShapePlan::SHARED))->toBe([]);
});

it('names a shape more than one kind reaches once, on the shared class', function (): void {
    $plan = new ShapePlan([
        'AEnvelope' => kindCarrying('Word', ['Word' => ['type' => 'string']]),
        'BEnvelope' => kindCarrying('Word', ['Word' => ['type' => 'string']]),
    ], []);

    expect($plan->owner('Word'))->toBe(ShapePlan::SHARED)
        ->and($plan->namedOn(ShapePlan::SHARED))->toHaveKey('Word')
        ->and($plan->namedOn('AEnvelope'))->toBe([]);
});

it('counts a shape reached through another as reached', function (): void {
    $plan = new ShapePlan([
        'AEnvelope' => kindCarrying('Outer', ['Outer' => refTo('Inner'), 'Inner' => ['type' => 'string']]),
    ], []);

    expect($plan->names('Inner'))->toBeTrue()
        ->and($plan->owner('Inner'))->toBe('AEnvelope');
});

it('names nothing no payload reaches', function (): void {
    $plan = new ShapePlan([
        'AEnvelope' => kindCarrying('Word', ['Word' => ['type' => 'string'], 'Spare' => ['type' => 'string']]),
    ], []);

    expect($plan->names('Spare'))->toBeFalse()
        ->and($plan->owner('Spare'))->toBeNull()
        ->and($plan->namedOn('AEnvelope'))->not->toHaveKey('Spare');
});

it('refuses a definition taking a name already held, and names it and its kind', function (string $name): void {
    expect(static fn(): ShapePlan => new ShapePlan(
        ['AEnvelope' => kindCarrying($name, [$name => ['type' => 'string']])],
        ['Kind'],
    ))->toThrow(UnexpectedValueException::class, sprintf('`%s`, carried by `AEnvelope`', $name));
})->with(['a generated class' => 'Kind', 'the payload alias' => ShapePlan::PAYLOAD, 'the shared class' => ShapePlan::SHARED]);

it('refuses one name carried as two shapes', function (): void {
    expect(static fn(): ShapePlan => new ShapePlan([
        'AEnvelope' => kindCarrying('Word', ['Word' => ['type' => 'string']]),
        'BEnvelope' => kindCarrying('Word', ['Word' => ['type' => 'integer']]),
    ], []))->toThrow(UnexpectedValueException::class, '`Word` is carried by more than one kind as two different shapes');
});

it('leaves a reference closing a cycle to be mixed, and no other', function (): void {
    $plan = new ShapePlan([
        'AEnvelope' => kindCarrying('Head', [
            'Head' => refTo('Loop'),
            'Loop' => ['properties' => ['back' => refTo('Pool'), 'self' => refTo('Loop')]],
            'Pool' => refTo('Loop'),
        ]),
    ], []);

    expect($plan->closesACycle('Loop', 'Loop'))->toBeTrue()
        ->and($plan->closesACycle('Loop', 'Pool'))->toBeTrue()
        ->and($plan->closesACycle('Pool', 'Loop'))->toBeTrue()
        ->and($plan->closesACycle('Head', 'Loop'))->toBeFalse()
        ->and($plan->closesACycle(null, 'Loop'))->toBeFalse();
});

it('imports what a class uses from elsewhere, and nothing it names itself', function (): void {
    $plan = new ShapePlan([
        'AEnvelope' => kindCarrying('Own', ['Own' => refTo('Common'), 'Common' => ['type' => 'string']]),
        'BEnvelope' => kindCarrying('Common', ['Common' => ['type' => 'string']]),
    ], []);

    expect($plan->importsFor('AEnvelope', [['$ref' => '#/$defs/Own'], ['type' => 'string']]))->toBe([])
        ->and($plan->importsFor('AEnvelope', array_values($plan->namedOn('AEnvelope'))))->toBe(['Common' => ShapePlan::SHARED])
        ->and($plan->importsFor(ShapePlan::SHARED, [refTo('Own')]))->toBe(['Own' => 'AEnvelope']);
});

it('reads each local reference once, in order, and none inside nested definitions or elsewhere', function (): void {
    $plan = new ShapePlan([], []);

    expect($plan->referencesIn([
        'anyOf' => [refTo('B'), refTo('A'), refTo('B'), ['$ref' => 'https://example.test/x']],
        '$defs' => ['C' => refTo('C')],
    ]))->toBe(['A', 'B']);
});
