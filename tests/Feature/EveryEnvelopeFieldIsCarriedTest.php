<?php

declare(strict_types=1);

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Tests\Support\ContractTree;
use Lemonfiber\Sdk\Tests\Support\EnvelopeFields;

it('carries every field the vendored contract gives the envelope', function (): void {
    $declared = EnvelopeFields::declaredUnder(__DIR__ . '/../../contract/web-api');

    expect($declared)->toBe(['api_version', 'data', 'host', 'job', 'kind'])
        ->and(EnvelopeFields::missingFrom(Envelope::class, $declared))->toBe([]);
});

it('names a field a kind gives the envelope that the envelope does not carry', function (): void {
    $tree = ContractTree::withDirectory([
        'index.json' => ['api_version' => 1, 'kinds' => ['word' => 'kinds/word.json', 'step' => 'kinds/step.json']],
        'kinds/word.json' => ['type' => 'object', 'properties' => ['api_version' => [], 'kind' => [], 'data' => []]],
        'kinds/step.json' => ['type' => 'object', 'properties' => ['api_version' => [], 'kind' => [], 'data' => [], 'said_by' => []]],
    ]);

    $declared = EnvelopeFields::declaredUnder($tree . '/contract/web-api');

    expect($declared)->toBe(['api_version', 'data', 'kind', 'said_by'])
        ->and(EnvelopeFields::missingFrom(Envelope::class, $declared))->toBe(['said_by']);

    ContractTree::remove($tree);
});

it('carries a snake-case field in the camel-case property', function (): void {
    expect(EnvelopeFields::asProperty('api_version'))->toBe('apiVersion')
        ->and(EnvelopeFields::asProperty('job'))->toBe('job');
});

/**
 * A contract directory broken in the way it is named for, by path in it.
 *
 * @return array<string, mixed>
 */
function aContractBroken(string $how): array
{
    $index = ['api_version' => 1, 'kinds' => ['word' => 'kinds/word.json']];

    return match ($how) {
        'no kinds' => ['index.json' => ['api_version' => 1]],
        'a kind by number' => ['index.json' => ['api_version' => 1, 'kinds' => ['word' => 7]]],
        'a kind with no properties' => ['index.json' => $index, 'kinds/word.json' => ['type' => 'object']],
        default => ['index.json' => $index, 'kinds/word.json' => 'not json'],
    };
}

it('refuses a directory it cannot read as a contract', function (string $how, string $expected): void {
    $tree = ContractTree::withDirectory(aContractBroken($how));

    expect(fn(): array => EnvelopeFields::declaredUnder($tree . '/contract/web-api'))
        ->toThrow(LogicException::class, $expected);

    ContractTree::remove($tree);
})->with([
    ['no kinds', 'names no kinds'],
    ['a kind by number', 'by something other than a path'],
    ['a kind with no properties', 'declares no properties'],
    ['a kind that is not JSON', 'is not a JSON object'],
]);
