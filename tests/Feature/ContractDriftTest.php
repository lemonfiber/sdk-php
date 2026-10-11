<?php

declare(strict_types=1);

use Lemonfiber\Sdk\Tests\Support\Archive;
use Lemonfiber\Sdk\Tests\Support\ContractTree;

/**
 * The comparison `contract-bump` makes, shown agreeing and refusing.
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
 * How the comparison exited and everything it said.
 *
 * @return array{int, string}
 */
function compared(string $tree, string $archive): array
{
    file_put_contents($tree . '/lemonfiber.tar.gz', $archive);

    $said = [];
    $code = 0;
    exec(sprintf(
        'cd %s && python3 %s 2>&1',
        escapeshellarg($tree),
        escapeshellarg((string) realpath(DRIFT)),
    ), $said, $code);

    return [$code, implode("\n", $said)];
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
