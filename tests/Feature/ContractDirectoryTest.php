<?php

declare(strict_types=1);

use Lemonfiber\Sdk\Scripts\VendoredContract;
use Lemonfiber\Sdk\Tests\Support\ContractTree;

/**
 * The generator reading the contract as a directory: an index, a file per
 * kind, a file per definition, and each `$ref` a path resolved against the
 * file it is in.
 */
const DIALECT = 'https://json-schema.org/draft/2020-12/schema';

/**
 * The envelope a kind's schema is, with the payload given.
 *
 * @param  array<string, mixed>  $data
 * @return array<string, mixed>
 */
function envelopeOf(array $data): array
{
    return [
        '$schema' => DIALECT,
        'title' => 'Envelope',
        'type' => 'object',
        'properties' => [
            'api_version' => ['type' => 'integer'],
            'data' => $data,
            'kind' => ['type' => 'string'],
        ],
        'required' => ['api_version', 'kind', 'data'],
    ];
}

/**
 * Two kinds sharing a definition, one reaching it through another, and one
 * definition holding itself.
 *
 * @return array<string, mixed>
 */
function aDirectory(): array
{
    return [
        'index.json' => [
            'api_version' => 1,
            'key_callable' => 'key-callable.json',
            'kinds' => ['word' => 'kinds/word.json', 'phrase' => 'kinds/phrase.json'],
            'reads' => 'reads.json',
            'refusals' => 'refusals.json',
        ],
        'kinds/word.json' => envelopeOf(['description' => 'The payload.', '$ref' => '../defs/Word.json']),
        'kinds/phrase.json' => envelopeOf(['$ref' => '../defs/Phrase.json']),
        'defs/Word.json' => ['$schema' => DIALECT, 'type' => 'object', 'properties' => ['text' => ['type' => 'string'], 'code' => ['$ref' => 'Code.json']], 'required' => ['text']],
        'defs/Code.json' => ['$schema' => DIALECT, 'enum' => ['plain', 'loud']],
        'defs/Phrase.json' => ['$schema' => DIALECT, 'type' => 'object', 'properties' => [
            'words' => ['type' => 'array', 'items' => ['$ref' => 'Word.json']],
            'within' => ['anyOf' => [['$ref' => 'Phrase.json'], ['type' => 'null']]],
        ]],
        'key-callable.json' => [['action' => 'restart', 'disturbs' => true, 'rehearsal' => true]],
        'reads.json' => [['path' => '/api/word', 'kinds' => ['word']]],
        'refusals.json' => ['ADMIT-4' => ['name' => 'NOT_ADMITTED', 'status' => 403, 'description' => 'Raised.']],
    ];
}

/**
 * What `aDirectory()` holds, written as the single file.
 *
 * @return array<string, mixed>
 */
function theSameAsOneFile(): array
{
    $word = ['type' => 'object', 'properties' => ['text' => ['type' => 'string'], 'code' => ['$ref' => '#/$defs/Code']], 'required' => ['text']];
    $code = ['enum' => ['plain', 'loud']];
    $phrase = ['type' => 'object', 'properties' => [
        'words' => ['type' => 'array', 'items' => ['$ref' => '#/$defs/Word']],
        'within' => ['anyOf' => [['$ref' => '#/$defs/Phrase'], ['type' => 'null']]],
    ]];

    return [
        'api_version' => 1,
        'key_callable' => [['action' => 'restart', 'disturbs' => true, 'rehearsal' => true]],
        'kinds' => [
            'phrase' => envelopeOf(['$ref' => '#/$defs/Phrase']) + ['$defs' => ['Code' => $code, 'Phrase' => $phrase, 'Word' => $word]],
            'word' => envelopeOf(['description' => 'The payload.', '$ref' => '#/$defs/Word']) + ['$defs' => ['Code' => $code, 'Word' => $word]],
        ],
        'reads' => [['path' => '/api/word', 'kinds' => ['word']]],
        'refusals' => ['ADMIT-4' => ['name' => 'NOT_ADMITTED', 'status' => 403, 'description' => 'Raised.']],
    ];
}

/**
 * Every file the generator wrote in a tree, by name.
 *
 * @return array<string, string>
 */
function generatedIn(string $tree): array
{
    $files = [];
    $found = glob($tree . '/src/Generated/*.php');

    foreach ($found === false ? [] : $found as $path) {
        $files[basename($path)] = (string) file_get_contents($path);
    }

    return $files;
}

it('generates from the directory the same bytes the single file generates', function (): void {
    $directory = ContractTree::withDirectory(aDirectory());
    $file = ContractTree::withFile(json_encode(theSameAsOneFile(), JSON_THROW_ON_ERROR));

    expect(ContractTree::generate($directory)['status'])->toBe(0)
        ->and(ContractTree::generate($file)['status'])->toBe(0)
        ->and(generatedIn($directory))->toBe(generatedIn($file))
        ->and(array_keys(generatedIn($directory)))->toContain('WordEnvelope.php', 'PhraseEnvelope.php')
        ->and(generatedIn($directory)['PhraseEnvelope.php'])
        ->toContain('@phpstan-type Phrase array{words?: list<Word>, within?: mixed}')
        ->and(generatedIn($directory)['Shapes.php'])
        ->toContain('@phpstan-type Word array{text: string, code?: Code}');

    ContractTree::remove($directory);
    ContractTree::remove($file);
});

it('reads the directory into the single file\'s shape', function (): void {
    $tree = ContractTree::withDirectory(aDirectory());

    $artefact = new VendoredContract($tree)->artefact();
    $expected = theSameAsOneFile();

    expect($artefact['kinds'] ?? null)->toEqual($expected['kinds'])
        ->and($artefact['refusals'] ?? null)->toBe($expected['refusals'])
        ->and($artefact['reads'] ?? null)->toBe($expected['reads'])
        ->and($artefact['key_callable'] ?? null)->toBe($expected['key_callable'])
        ->and(new VendoredContract($tree)->source())->toBe('contract/web-api/');

    ContractTree::remove($tree);
});

it('refuses a reference in the directory that resolves to no definition, names it and the file, and writes nothing', function (string $at, string $reference): void {
    $files = aDirectory();
    $files[$at] = $at === 'kinds/word.json'
        ? envelopeOf(['$ref' => $reference])
        : ['$schema' => DIALECT, 'type' => 'object', 'properties' => ['code' => ['$ref' => $reference]]];
    $tree = ContractTree::withDirectory($files);

    $result = ContractTree::generate($tree);

    expect($result['status'])->toBe(1)
        ->and($result['stderr'])->toContain('contract/web-api/' . $at . ' -> "' . $reference . '"')
        ->and($result['stderr'])->toContain('Nothing was generated.')
        ->and(is_dir($tree . '/src/Generated'))->toBeFalse();

    ContractTree::remove($tree);
})->with([
    'a definition not there, from a kind' => ['kinds/word.json', '../defs/Gone.json'],
    'a pointer into the kind itself' => ['kinds/word.json', '#/$defs/Word'],
    'a path not under defs' => ['kinds/word.json', 'phrase.json'],
    'a path climbing out of the directory' => ['kinds/word.json', '../../web-api.contract.json'],
    'a path climbing out of the tree' => ['kinds/word.json', '../../../../defs/Word.json'],
    'a definition with a fragment' => ['kinds/word.json', '../defs/Word.json#/properties/text'],
    'an address' => ['kinds/word.json', 'https://example.org/defs/Word.json'],
    'a file that is not a definition' => ['kinds/word.json', '../defs/Word.yaml'],
    'a definition not there, from a definition' => ['defs/Word.json', 'Gone.json'],
    'a path from a definition as if from a kind' => ['defs/Word.json', '../defs/../kinds/word.json'],
]);

it('refuses an unresolvable reference in a definition no kind reaches', function (): void {
    $files = aDirectory();
    $files['defs/Unused.json'] = ['$schema' => DIALECT, '$ref' => 'Gone.json'];
    $tree = ContractTree::withDirectory($files);

    $result = ContractTree::generate($tree);

    expect($result['status'])->toBe(1)
        ->and($result['stderr'])->toContain('contract/web-api/defs/Unused.json -> "Gone.json"')
        ->and(is_dir($tree . '/src/Generated'))->toBeFalse();

    ContractTree::remove($tree);
});

it('refuses what the single file is refused for, in the directory too', function (array $changed, string $named): void {
    /** @var array<string, mixed> $changed */
    $tree = ContractTree::withDirectory([...aDirectory(), ...$changed]);

    $result = ContractTree::generate($tree);

    expect($result['status'])->toBe(1)
        ->and($result['stderr'])->toContain($named)
        ->and(is_dir($tree . '/src/Generated'))->toBeFalse();

    ContractTree::remove($tree);
})->with([
    'a version it does not implement' => [
        ['index.json' => ['api_version' => 2, 'kinds' => ['word' => 'kinds/word.json']]],
        'api_version 2 and this package implements 1',
    ],
    'a constraint beside a reference' => [
        ['kinds/word.json' => envelopeOf(['$ref' => '../defs/Word.json', 'type' => 'object'])],
        '/word/properties/data (type)',
    ],
    'two kinds named alike' => [
        ['index.json' => ['api_version' => 1, 'kinds' => ['a-b' => 'kinds/word.json', 'a_b' => 'kinds/phrase.json']]],
        'would both be named AB',
    ],
    'no kinds' => [['index.json' => ['api_version' => 1, 'kinds' => []]], 'describes no kinds'],
    'a malformed list of refusals' => [['refusals.json' => ['ADMIT-4' => 'NOT_ADMITTED']], '`ADMIT-4` is not an object'],
    'an index that is not JSON' => [['index.json' => '{'], 'contract/web-api/index.json is not JSON'],
    'an index that is not an object' => [['index.json' => '7'], 'contract/web-api/index.json is not a JSON object'],
    'a kind file that is not there' => [
        ['index.json' => ['api_version' => 1, 'kinds' => ['word' => 'kinds/gone.json']]],
        'contract/web-api/kinds/gone.json could not be read',
    ],
    'a kind file outside the directory' => [
        ['index.json' => ['api_version' => 1, 'kinds' => ['word' => '../web-api.contract.json']]],
        'names "../web-api.contract.json" as the kind `word`, which is no file inside contract/web-api/',
    ],
    'a list file that is not text' => [
        ['index.json' => ['api_version' => 1, 'kinds' => ['word' => 'kinds/word.json'], 'refusals' => 7]],
        'names 7 as refusals',
    ],
    'a definition that is not an object' => [['defs/Code.json' => '[]'], 'contract/web-api/defs/Code.json is not a JSON object'],
]);

it('refuses a tree holding both layouts', function (): void {
    $tree = ContractTree::withDirectory(aDirectory());
    file_put_contents($tree . '/contract/web-api.contract.json', json_encode(theSameAsOneFile(), JSON_THROW_ON_ERROR));

    $result = ContractTree::generate($tree);

    expect($result['status'])->toBe(1)
        ->and($result['stderr'])->toContain('Both contract/web-api.contract.json and contract/web-api/ are vendored')
        ->and(is_dir($tree . '/src/Generated'))->toBeFalse();

    ContractTree::remove($tree);
});

it('refuses a tree holding neither layout', function (): void {
    $tree = ContractTree::withDirectory([]);

    $result = ContractTree::generate($tree);

    expect($result['status'])->toBe(1)
        ->and($result['stderr'])->toContain('There is no vendored contract at contract/web-api/ or contract/web-api.contract.json')
        ->and(is_dir($tree . '/src/Generated'))->toBeFalse();

    ContractTree::remove($tree);
});
