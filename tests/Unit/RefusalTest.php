<?php

declare(strict_types=1);

use Lemonfiber\Sdk\Refusal;
use Lemonfiber\Sdk\Remedy;

/**
 * A problem document as lemonfiber writes one, changed where a case says.
 *
 * @param  array<string, mixed>  $changed
 * @return array<string, mixed>
 */
function aProblemDocument(array $changed = []): array
{
    return [
        'code' => 'bundle.leak',
        'severity' => 'critical',
        'state' => 'guided',
        'summary' => 'The bundle still held something that reads as a credential',
        'meaning' => 'Nothing has been written.',
        'remedies' => [
            ['action' => 'Report which file this names'],
            ['action' => 'Send a diagnostic bundle', 'detail' => 'lemonfiber support'],
        ],
        'detail' => 'services.txt line 3 — nothing was written',
        ...$changed,
    ];
}

/**
 * The remedies of a refusal, each as its action and its detail.
 *
 * @return list<array{string, ?string}>
 */
function whatARefusalSaysToDo(Refusal $refusal): array
{
    return array_map(static fn(Remedy $remedy): array => [$remedy->action(), $remedy->detail()], $refusal->remedies());
}

it('reads every field of the problem document', function (): void {
    $refusal = Refusal::from(aProblemDocument());

    expect($refusal?->code())->toBe('bundle.leak')
        ->and($refusal?->severity())->toBe('critical')
        ->and($refusal?->state())->toBe('guided')
        ->and($refusal?->summary())->toBe('The bundle still held something that reads as a credential')
        ->and($refusal?->meaning())->toBe('Nothing has been written.')
        ->and($refusal?->detail())->toBe('services.txt line 3 — nothing was written')
        ->and($refusal?->cause())->toBeNull()
        ->and($refusal instanceof Refusal ? whatARefusalSaysToDo($refusal) : [])->toBe([
            ['Report which file this names', null],
            ['Send a diagnostic bundle', 'lemonfiber support'],
        ]);
});

it('leaves absent what lemonfiber left out, given as null or not given at all', function (array $document): void {
    $refusal = Refusal::from($document);

    expect($refusal)->toBeInstanceOf(Refusal::class)
        ->and($refusal?->detail())->toBeNull()
        ->and($refusal?->cause())->toBeNull();
})->with([
    'null' => [aProblemDocument(['detail' => null, 'cause' => null])],
    'not given' => [array_diff_key(aProblemDocument(), ['detail' => true])],
]);

it('reads the problem beneath this one, as a problem of its own', function (): void {
    $refusal = Refusal::from(aProblemDocument(['cause' => aProblemDocument([
        'code' => 'disk.full',
        'detail' => null,
        'remedies' => [],
    ])]));

    expect($refusal?->cause()?->code())->toBe('disk.full')
        ->and($refusal?->cause()?->detail())->toBeNull()
        ->and($refusal?->cause()?->remedies())->toBe([])
        ->and($refusal?->cause()?->cause())->toBeNull();
});

it('reads nothing from a document the contract does not describe', function (mixed $document): void {
    expect(Refusal::from($document))->toBeNull();
})->with([
    'not a table' => ['a sentence'],
    'no code' => [array_diff_key(aProblemDocument(), ['code' => true])],
    'a severity that is not text' => [aProblemDocument(['severity' => 3])],
    'no state' => [array_diff_key(aProblemDocument(), ['state' => true])],
    'no summary' => [array_diff_key(aProblemDocument(), ['summary' => true])],
    'no meaning' => [array_diff_key(aProblemDocument(), ['meaning' => true])],
    'no remedies' => [array_diff_key(aProblemDocument(), ['remedies' => true])],
    'remedies that are not a list' => [aProblemDocument(['remedies' => ['first' => ['action' => 'Wait']]])],
    'a remedy that is not a table' => [aProblemDocument(['remedies' => ['Wait']])],
    'a remedy with no action' => [aProblemDocument(['remedies' => [['detail' => 'soon']]])],
    'a remedy whose detail is not text' => [aProblemDocument(['remedies' => [['action' => 'Wait', 'detail' => 5]]])],
    'a detail that is not text' => [aProblemDocument(['detail' => ['services.txt']])],
    'a cause that is not a problem' => [aProblemDocument(['cause' => 'the disk'])],
    'a cause missing its code' => [aProblemDocument(['cause' => array_diff_key(aProblemDocument(), ['code' => true])])],
]);
