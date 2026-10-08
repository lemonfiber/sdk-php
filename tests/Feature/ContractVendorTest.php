<?php

declare(strict_types=1);

use Lemonfiber\Sdk\Scripts\ContractVendor;
use Lemonfiber\Sdk\Scripts\Tarball;
use Lemonfiber\Sdk\Tests\Support\Archive;
use Lemonfiber\Sdk\Tests\Support\ContractTree;

/**
 * Vendoring the contract a revision's archive holds, in whichever layout it holds it.
 */
const REVISION = '69260f08cd603adc348d1c01eb398ae6b02cd8da';

/**
 * The files of a contract directory, by path in the revision's tree.
 *
 * @return array<string, string>
 */
function aServedDirectory(): array
{
    return [
        'contract/web-api/index.json' => '{"api_version": 1, "key_callable": "key-callable.json", "kinds": {"word": "kinds/word.json"}, "reads": "reads.json", "refusals": "refusals.json"}',
        'contract/web-api/kinds/word.json' => '{"type": "object", "properties": {"data": {"$ref": "../defs/Word.json"}}}',
        'contract/web-api/defs/Word.json' => '{"type": "string"}',
        'contract/web-api/key-callable.json' => '[]',
        'contract/web-api/reads.json' => '[]',
        'contract/web-api/refusals.json' => '{}',
        'contract/web-api-surface/index.json' => '{"not": "the contract"}',
        'contract/adapters.json' => '{}',
        'README.md' => '# lemonfiber',
    ];
}

/**
 * The single file a revision holding no directory serves, as served.
 */
function aServedFile(): string
{
    return '{"api_version": 1, "kinds": {"word": {"type": "object"}, "phrase": {"type": "object"}}}';
}

/**
 * Every file under the tree's `contract/`, by path relative to it.
 *
 * @return array<string, string>
 */
function vendoredUnder(string $tree): array
{
    $files = [];
    $walk = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($tree . '/contract', FilesystemIterator::SKIP_DOTS));

    foreach ($walk as $file) {
        if ($file instanceof SplFileInfo && $file->isFile()) {
            $files[substr($file->getPathname(), strlen($tree . '/contract/'))] = (string) file_get_contents($file->getPathname());
        }
    }

    ksort($files);

    return $files;
}

it('vendors the directory where the revision holds one, and only it', function (): void {
    $tree = ContractTree::withFile(aServedFile());

    $vendored = new ContractVendor($tree)->vendor(Archive::of(aServedDirectory()), REVISION);

    expect($vendored)->toBe(['layout' => 'contract/web-api/', 'version' => 1, 'kinds' => ['word']])
        ->and(vendoredUnder($tree))->toBe([
            'VERSION' => REVISION . "\n",
            'web-api/defs/Word.json' => '{"type": "string"}',
            'web-api/index.json' => aServedDirectory()['contract/web-api/index.json'],
            'web-api/key-callable.json' => '[]',
            'web-api/kinds/word.json' => aServedDirectory()['contract/web-api/kinds/word.json'],
            'web-api/reads.json' => '[]',
            'web-api/refusals.json' => '{}',
        ])
        ->and(ContractVendor::summary(REVISION, $vendored))->toContain('vendored ' . REVISION . ' as contract/web-api/, api_version 1, 1 kinds');

    ContractTree::remove($tree);
});

it('replaces the directory whole, so a definition the revision dropped leaves', function (): void {
    $tree = ContractTree::withDirectory(['defs/Dropped.json' => '{}', 'index.json' => '{}']);

    new ContractVendor($tree)->vendor(Archive::of(aServedDirectory()), REVISION);

    expect(array_keys(vendoredUnder($tree)))->not->toContain('web-api/defs/Dropped.json')
        ->and(array_keys(vendoredUnder($tree)))->toContain('web-api/defs/Word.json');

    ContractTree::remove($tree);
});

it('vendors the single file where the revision holds no directory, and removes the directory', function (): void {
    $tree = ContractTree::withDirectory(['index.json' => '{}']);

    $vendored = new ContractVendor($tree)->vendor(Archive::of(['contract/web-api.contract.json' => aServedFile()]), 'v1.2.3');

    expect($vendored)->toBe(['layout' => 'contract/web-api.contract.json', 'version' => 1, 'kinds' => ['word', 'phrase']])
        ->and(vendoredUnder($tree))->toBe(['VERSION' => "v1.2.3\n", 'web-api.contract.json' => aServedFile() . "\n"]);

    ContractTree::remove($tree);
});

it('takes the directory where the revision holds both', function (): void {
    $tree = ContractTree::withFile(aServedFile());

    $vendored = new ContractVendor($tree)->vendor(Archive::of([...aServedDirectory(), 'contract/web-api.contract.json' => aServedFile()]), REVISION);

    expect($vendored['layout'])->toBe('contract/web-api/')
        ->and(array_keys(vendoredUnder($tree)))->not->toContain('web-api.contract.json');

    ContractTree::remove($tree);
});

it('refuses an archive it cannot vendor, names what is wrong, and leaves the copy as it was', function (array $files, string $named): void {
    /** @var array<string, string> $files */
    $tree = ContractTree::withFile(aServedFile());
    $before = vendoredUnder($tree);

    expect(fn(): array => new ContractVendor($tree)->vendor(Archive::of($files), REVISION))
        ->toThrow(UnexpectedValueException::class, $named)
        ->and(vendoredUnder($tree))->toBe($before);

    ContractTree::remove($tree);
})->with([
    'no contract at all' => [['README.md' => '#'], 'holds neither contract/web-api/ nor contract/web-api.contract.json'],
    'a directory with no index' => [['contract/web-api/kinds/word.json' => '{}'], 'holds contract/web-api/ with no index.json in it'],
    'an index that is not JSON' => [['contract/web-api/index.json' => '{'], 'contract/web-api/index.json at ' . REVISION . ' is not JSON'],
    'an index that is not an object' => [['contract/web-api/index.json' => '7'], 'is not a contract artefact'],
    'an index naming no version' => [['contract/web-api/index.json' => '{"kinds": {"word": "kinds/word.json"}}'], 'is not a contract artefact'],
    'an index naming no kinds' => [['contract/web-api/index.json' => '{"api_version": 1, "kinds": {}}'], 'describes no kinds'],
    'an index naming a kind by number' => [['contract/web-api/index.json' => '{"api_version": 1, "kinds": ["kinds/word.json"]}'], 'names a kind that is not a word'],
    'an index naming a kind file not there' => [
        ['contract/web-api/index.json' => '{"api_version": 1, "kinds": {"word": "kinds/gone.json"}}'],
        'names "kinds/gone.json", which the revision does not hold under contract/web-api/',
    ],
    'an index naming a list file not there' => [
        [...aServedDirectory(), 'contract/web-api/index.json' => '{"api_version": 1, "kinds": {"word": "kinds/word.json"}, "reads": "gone.json"}'],
        'names "gone.json"',
    ],
    'an index naming an action file not there' => [
        [...aServedDirectory(), 'contract/web-api/index.json' => '{"api_version": 1, "kinds": {"word": "kinds/word.json"}, "actions": {"restart": "actions/gone.json"}}'],
        'names "actions/gone.json"',
    ],
    'a file in the directory that is not JSON' => [[...aServedDirectory(), 'contract/web-api/defs/Broken.json' => '{'], 'contract/web-api/defs/Broken.json at ' . REVISION . ' is not JSON'],
    'a single file that is not JSON' => [['contract/web-api.contract.json' => 'nope'], 'contract/web-api.contract.json at ' . REVISION . ' is not JSON'],
    'a single file naming no kinds' => [['contract/web-api.contract.json' => '{"api_version": 1}'], 'is not a contract artefact'],
    'a path climbing out of the directory' => [['contract/web-api/../escape.json' => '{}'], 'which is no plain path under contract/web-api/'],
]);

it('reads a path too long for the header from the pax record naming it', function (): void {
    $long = 'contract/web-api/defs/' . str_repeat('Long', 30) . '.json';

    expect(new Tarball()->filesUnder(Archive::of([$long => '{}']), 'contract/web-api'))
        ->toBe(['defs/' . str_repeat('Long', 30) . '.json' => '{}']);
});

it('reads only regular files', function (): void {
    expect(new Tarball()->filesUnder(Archive::of(['contract/web-api/index.json' => '{}'], ['contract/web-api/defs/Link.json' => '/etc/passwd']), 'contract/web-api'))
        ->toBe(['index.json' => '{}']);
});

it('refuses what is not an archive', function (string $fetched): void {
    expect(fn(): array => new Tarball()->filesUnder($fetched, 'contract/web-api'))->toThrow(UnexpectedValueException::class);
})->with([
    'not gzipped' => ['<html>'],
    'cut short' => [(string) gzencode(str_repeat("\0", 100))],
    'a size not in octal' => [(string) gzencode(str_pad('a', 124, "\0") . str_pad('zz', 388, "\0") . str_repeat("\0", 1024))],
]);
