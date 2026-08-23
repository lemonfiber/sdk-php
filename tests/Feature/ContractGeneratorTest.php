<?php

declare(strict_types=1);

/**
 * The generator's refusal, exercised as the command it is.
 *
 * The script resolves its paths from its own directory, so a copy of it and
 * its helper inside a temporary tree reads that tree's contract and writes
 * into that tree.
 */
$root = dirname(__DIR__, 2);

/**
 * A contract with one kind, so only the version is ever what is wrong.
 *
 * @return array<string, mixed>
 */
function contractOf(int $apiVersion): array
{
    return [
        'api_version' => $apiVersion,
        'kinds' => [
            'word' => [
                'type' => 'object',
                'properties' => [
                    'api_version' => ['type' => 'integer'],
                    'kind' => ['type' => 'string'],
                    'data' => ['type' => 'object', 'properties' => ['word' => ['type' => 'string']]],
                ],
                'required' => ['api_version', 'kind', 'data'],
            ],
        ],
    ];
}

/**
 * A tree holding the generator, its helper, and the contract given to it.
 */
function treeWith(string $root, string $contract): string
{
    $tree = $root . '/.contract-test-' . bin2hex(random_bytes(6));

    mkdir($tree . '/scripts', 0o755, true);
    mkdir($tree . '/contract', 0o755, true);
    copy($root . '/scripts/contract-generate.php', $tree . '/scripts/contract-generate.php');
    copy($root . '/scripts/SchemaTypes.php', $tree . '/scripts/SchemaTypes.php');
    file_put_contents($tree . '/contract/web-api.contract.json', $contract);
    file_put_contents($tree . '/contract/VERSION', "v9.9.9\n");

    return $tree;
}

/**
 * @return array{status: int, stderr: string}
 */
function generateIn(string $tree): array
{
    $pipes = [];
    $process = proc_open(
        [PHP_BINARY, $tree . '/scripts/contract-generate.php'],
        [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
        $pipes,
    );

    if ($process === false) {
        throw new RuntimeException('The generator could not be started.');
    }

    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);

    return ['status' => proc_close($process), 'stderr' => is_string($stderr) ? $stderr : ''];
}

function removeTree(string $tree): void
{
    exec('rm -rf ' . escapeshellarg($tree));
}

it('refuses a version it does not implement, and names both', function () use ($root): void {
    $tree = treeWith($root, json_encode(contractOf(2), JSON_THROW_ON_ERROR));

    $result = generateIn($tree);

    expect($result['status'])->toBe(1)
        ->and($result['stderr'])->toContain('2')
        ->and($result['stderr'])->toContain('1');

    removeTree($tree);
});

it('writes nothing when it refuses', function () use ($root): void {
    $tree = treeWith($root, json_encode(contractOf(2), JSON_THROW_ON_ERROR));

    generateIn($tree);

    expect(is_dir($tree . '/src/Generated'))->toBeFalse();

    removeTree($tree);
});

it('writes when the version is the one it implements', function () use ($root): void {
    $tree = treeWith($root, json_encode(contractOf(1), JSON_THROW_ON_ERROR));

    $result = generateIn($tree);

    expect($result['status'])->toBe(0)
        ->and(is_file($tree . '/src/Generated/Contract.php'))->toBeTrue();

    removeTree($tree);
});

it('refuses a contract describing no kinds', function () use ($root): void {
    $tree = treeWith($root, json_encode(['api_version' => 1, 'kinds' => []], JSON_THROW_ON_ERROR));

    $result = generateIn($tree);

    expect($result['status'])->toBe(1)
        ->and(is_dir($tree . '/src/Generated'))->toBeFalse();

    removeTree($tree);
});
