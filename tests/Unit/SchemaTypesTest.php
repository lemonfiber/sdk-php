<?php

declare(strict_types=1);

use Lemonfiber\Sdk\Scripts\SchemaTypes;
use Lemonfiber\Sdk\Scripts\ShapePlan;

/**
 * The types a plan over one kind carrying `$defs` gives, with `Word` its payload.
 *
 * @param  array<mixed, mixed>  $defs
 */
function typesCarrying(array $defs): SchemaTypes
{
    return new SchemaTypes(new ShapePlan(['WordEnvelope' => [
        '$defs' => $defs,
        'properties' => ['data' => ['$ref' => '#/$defs/Word']],
    ]], []));
}

it('refuses rather than types as mixed a reference it cannot resolve', function (mixed $reference, array $defs): void {
    expect(fn(): string => typesCarrying($defs)->typeOf(['$ref' => $reference]))
        ->toThrow(UnexpectedValueException::class, 'The reference ' . json_encode($reference, JSON_UNESCAPED_SLASHES) . ' resolves to no definition');
})->with([
    'a path to a file' => ['../defs/Word.json', ['Word' => ['type' => 'string']]],
    'a definition not carried' => ['#/$defs/Phrase', ['Word' => ['type' => 'string']]],
    'a definition that is not a schema' => ['#/$defs/Word', ['Word' => 'string']],
    'a reference that is not text' => [null, ['Word' => ['type' => 'string']]],
]);

it('types a reference to a definition it carries as that definition\'s alias', function (): void {
    expect(typesCarrying(['Word' => ['type' => 'string']])->typeOf(['$ref' => '#/$defs/Word']))->toBe('Word');
});

it('refuses rather than drops a keyword it does not read, wherever it is nested', function (array $schema, string $unread): void {
    expect(fn(): string => typesCarrying([])->typeOf($schema, 'RunningCopy'))
        ->toThrow(UnexpectedValueException::class, "A schema in RunningCopy uses {$unread}, which this generator does not read.");
})->with([
    'two structures flattened into one' => [['type' => 'object', 'allOf' => [['type' => 'object'], ['type' => 'object']]], 'allOf'],
    'a field of a field' => [['type' => 'object', 'properties' => ['state' => ['not' => ['const' => 'working']]]], 'not'],
]);

it('types each branch with the fields the schema holds beside the branches', function (): void {
    expect(typesCarrying([])->typeOf([
        'type' => 'object',
        'properties' => ['part' => ['type' => 'string']],
        'required' => ['part'],
        'oneOf' => [
            ['type' => 'object', 'properties' => ['state' => ['const' => 'working']], 'required' => ['state']],
            ['type' => 'object', 'properties' => ['state' => ['const' => 'paced'], 'limit' => ['type' => 'integer']], 'required' => ['state', 'limit']],
        ],
    ]))->toBe("array{part: string, state: 'working'}|array{part: string, state: 'paced', limit: int}");
});

it('refuses fields beside a branch they cannot join', function (mixed $branch): void {
    expect(fn(): string => typesCarrying(['Word' => ['type' => 'string']])->typeOf([
        'properties' => ['part' => ['type' => 'string']],
        'oneOf' => [$branch],
    ], 'RunningCopy'))->toThrow(UnexpectedValueException::class, 'A schema in RunningCopy holds fields beside a branch they cannot join.');
})->with([
    'a reference' => [['$ref' => '#/$defs/Word']],
    'no schema' => ['working'],
    'fields that are not fields' => [['properties' => 'state']],
]);
