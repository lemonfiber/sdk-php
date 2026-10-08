<?php

declare(strict_types=1);

use Lemonfiber\Sdk\Tests\Support\Archive;
use Lemonfiber\Sdk\Tests\Support\ContractTree;

/**
 * The comparison `contract-drift` and `contract-bump` make, shown agreeing and refusing.
 */
const DRIFT = __DIR__ . '/../../scripts/contract_drift.py';

/**
 * A contract directory, by path in the revision's tree.
 *
 * @return array<string, string>
 */
function aDriftDirectory(): array
{
    return [
        'contract/web-api/index.json' => '{"api_version": 1, "kinds": {"word": "kinds/word.json"}}',
        'contract/web-api/kinds/word.json' => '{"type": "object", "properties": {"data": {"$ref": "../defs/Word.json"}}}',
        'contract/web-api/defs/Word.json' => '{"type": "string"}',
    ];
}

/**
 * The same directory as a vendored tree, by path in it.
 *
 * @param  array<string, string>  $files  by path in the revision's tree
 * @return array<string, string>
 */
function vendoredAs(array $files): array
{
    $vendored = [];

    foreach ($files as $path => $contents) {
        $vendored[substr($path, strlen('contract/web-api/'))] = $contents;
    }

    return $vendored;
}

/**
 * How the comparison exited and everything it said, with what it wrote to the run page.
 *
 * @return array{int, string, string}
 */
function compared(string $tree, string $archive, string ...$flags): array
{
    $served = $tree . '/lemonfiber.tar.gz';
    $summary = $tree . '/summary.md';
    file_put_contents($served, $archive);

    $said = [];
    $code = 0;
    exec(sprintf(
        'cd %s && GITHUB_STEP_SUMMARY=%s python3 %s %s 2>&1',
        escapeshellarg($tree),
        escapeshellarg($summary),
        escapeshellarg((string) realpath(DRIFT)),
        implode(' ', array_map(escapeshellarg(...), $flags)),
    ), $said, $code);

    return [$code, implode("\n", $said), is_file($summary) ? (string) file_get_contents($summary) : ''];
}

it('agrees where each file parses to the same JSON, however it is spelled', function (): void {
    $vendored = vendoredAs(aDriftDirectory());
    $vendored['defs/Word.json'] = "{\n  \"type\":   \"string\"\n}\n";
    $tree = ContractTree::withDirectory($vendored);

    [$code, $said] = compared($tree, Archive::of(aDriftDirectory()));

    expect($code)->toBe(0)
        ->and($said)->toContain('is the one the revision holds');

    ContractTree::remove($tree);
});

it('names each file that differs, is missing, or is left over', function (array $served, array $named): void {
    /** @var array<string, string> $served */
    $tree = ContractTree::withDirectory(vendoredAs(aDriftDirectory()));

    [$code, $said] = compared($tree, Archive::of($served));

    expect($code)->toBe(1);

    foreach ($named as $path) {
        expect($said)->toContain($path);
    }

    ContractTree::remove($tree);
})->with([
    'a definition changed' => [[...aDriftDirectory(), 'contract/web-api/defs/Word.json' => '{"type": "integer"}'], ['1 files differ', 'contract/web-api/defs/Word.json']],
    'a definition added' => [[...aDriftDirectory(), 'contract/web-api/defs/Code.json' => '{}'], ['1 files differ', 'contract/web-api/defs/Code.json']],
    'a definition dropped' => [array_diff_key(aDriftDirectory(), ['contract/web-api/defs/Word.json' => true]), ['1 files differ', 'contract/web-api/defs/Word.json']],
]);

it('compares the single file where the revision holds no directory', function (): void {
    $contract = '{"api_version": 1, "kinds": {"word": {}}}';
    $tree = ContractTree::withFile($contract);

    [$same] = compared($tree, Archive::of(['contract/web-api.contract.json' => $contract]));
    [$moved, $said] = compared($tree, Archive::of(['contract/web-api.contract.json' => '{"api_version": 1, "kinds": {"phrase": {}}}']));

    expect($same)->toBe(0)
        ->and($moved)->toBe(1)
        ->and($said)->toContain('contract/web-api.contract.json');

    ContractTree::remove($tree);
});

it('differs in every file where the copy is in the other layout', function (): void {
    $tree = ContractTree::withFile('{"api_version": 1, "kinds": {"word": {}}}');

    [$code, $said] = compared($tree, Archive::of(aDriftDirectory()));

    expect($code)->toBe(1)
        ->and($said)->toContain('4 files differ')
        ->and($said)->toContain('contract/web-api.contract.json')
        ->and($said)->toContain('contract/web-api/index.json');

    ContractTree::remove($tree);
});

it('reports the drift on the run page with the remedy, naming the revision and the files', function (): void {
    $tree = ContractTree::withDirectory(vendoredAs(aDriftDirectory()));
    $served = [...aDriftDirectory(), 'contract/web-api/index.json' => '{"api_version": 1, "kinds": {"word": "kinds/word.json", "phrase": "kinds/word.json"}}'];

    [$code, $said, $summary] = compared($tree, Archive::of($served), '--head', 'abc123', '--report');

    expect($code)->toBe(1)
        ->and($summary)->toContain('## The vendored contract is behind')
        ->and($summary)->toContain('described by the server and not here: phrase')
        ->and($summary)->toContain('`contract/web-api/index.json`')
        ->and($summary)->toContain('composer contract:sync -- abc123')
        ->and($said)->toContain('::error::the vendored contract is behind lemonfiber abc123');

    [$current, , $agreed] = compared($tree, Archive::of(aDriftDirectory()), '--head', 'abc123', '--report');

    expect($current)->toBe(0)
        ->and($agreed)->toContain('## The vendored contract is current');

    ContractTree::remove($tree);
});

it('stops, rather than agreeing or differing, where a copy cannot be read', function (string $archive, string $named): void {
    $tree = ContractTree::withDirectory(vendoredAs(aDriftDirectory()));

    [$code, $said] = compared($tree, $archive);

    expect($code)->toBe(2)
        ->and($said)->toContain($named);

    ContractTree::remove($tree);
})->with([
    'not an archive' => ['<html>', 'is not an archive this can read'],
    'no contract in it' => [Archive::of(['README.md' => '#']), 'holds neither contract/web-api/index.json nor contract/web-api.contract.json'],
    'a directory with no index' => [Archive::of(['contract/web-api/defs/Word.json' => '{}']), 'with no index.json in it'],
    'a file that is not JSON' => [Archive::of([...aDriftDirectory(), 'contract/web-api/defs/Word.json' => '{']), 'defs/Word.json is not JSON'],
]);
