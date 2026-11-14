<?php

declare(strict_types=1);

use Lemonfiber\Sdk\Scripts\ActionSource;
use Lemonfiber\Sdk\Scripts\GeneratedSource;
use Lemonfiber\Sdk\Tests\Support\ContractTree;

/**
 * A contract whose one kind, `word`, carries the string definition `$name` as its payload.
 *
 * @return array<string, mixed>
 */
function contractDefining(string $name): array
{
    return [
        'api_version' => 1,
        'kinds' => [
            'word' => [
                'type' => 'object',
                '$defs' => [$name => ['type' => 'string']],
                'properties' => [
                    'api_version' => ['type' => 'integer'],
                    'kind' => ['type' => 'string'],
                    'data' => ['$ref' => '#/$defs/' . $name],
                ],
                'required' => ['api_version', 'kind', 'data'],
            ],
        ],
    ];
}

it('refuses a definition taking a name a generated file imports or a type it writes spells, naming it and its kind', function (string $name): void {
    $tree = ContractTree::withFile(json_encode(contractDefining($name), JSON_THROW_ON_ERROR));

    $result = ContractTree::generate($tree);

    expect($result['status'])->toBe(1)
        ->and($result['stderr'])->toContain(sprintf('The definition `%s`, carried by `word`, takes a name this generator writes itself.', $name))
        ->and(is_dir($tree . '/src/Generated'))->toBeFalse();

    ContractTree::remove($tree);
})->with(['an envelope class import' => 'Envelope', 'an import in another case' => 'payload', 'an action class import' => 'ActionRequest', 'a type keyword' => 'Mixed']);

it('reserves every class a committed generated file imports', function (): void {
    $imported = [];
    $files = glob(dirname(__DIR__, 2) . '/src/Generated/*.php');

    foreach (is_array($files) ? $files : [] as $file) {
        preg_match_all('/^use [\w\\\\]+\\\\(\w+);$/m', (string) file_get_contents($file), $found);
        $imported = [...$imported, ...$found[1]];
    }

    expect($imported)->not->toBeEmpty()
        ->and(array_values(array_diff(array_unique($imported), [...GeneratedSource::IMPORTED, ...ActionSource::IMPORTED])))->toBe([]);
});
