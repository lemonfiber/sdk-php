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
