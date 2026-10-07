<?php

declare(strict_types=1);

use Lemonfiber\Sdk\Scripts\LineCap;

/**
 * The generator's refusal, exercised as the command it is.
 *
 * The script resolves its paths from its own directory, so a copy of it and
 * its helper inside a temporary tree reads that tree's contract and writes
 * into that tree.
 */
$root = dirname(__DIR__, 2);

const GENERATOR = '/scripts/contract-generate.php';

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
 * A tree holding the generator, its helpers, and the contract given to it.
 */
function treeWith(string $root, string $contract): string
{
    $tree = $root . '/.contract-test-' . bin2hex(random_bytes(6));

    mkdir($tree . '/scripts', 0o755, true);
    mkdir($tree . '/contract', 0o755, true);
    copy($root . GENERATOR, $tree . GENERATOR);
    copy($root . '/scripts/GeneratedSource.php', $tree . '/scripts/GeneratedSource.php');
    copy($root . '/scripts/SchemaTypes.php', $tree . '/scripts/SchemaTypes.php');
    copy($root . '/scripts/Refusals.php', $tree . '/scripts/Refusals.php');
    copy($root . '/scripts/KeyCallable.php', $tree . '/scripts/KeyCallable.php');
    copy($root . '/scripts/LineCap.php', $tree . '/scripts/LineCap.php');
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
        [PHP_BINARY, $tree . GENERATOR],
        [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
        $pipes,
    );

    if ($process === false) {
        return ['status' => -1, 'stderr' => 'The generator could not be started.'];
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

/**
 * A contract whose one kind describes its payload by reference.
 *
 * @param  array<string, mixed>  $beside  what sits beside the reference
 * @return array<string, mixed>
 */
function contractPointingAt(array $beside): array
{
    return [
        'api_version' => 1,
        'kinds' => [
            'word' => [
                'type' => 'object',
                '$defs' => ['Word' => ['type' => 'object']],
                'properties' => [
                    'api_version' => ['type' => 'integer'],
                    'kind' => ['type' => 'string'],
                    'data' => ['$ref' => '#/$defs/Word'] + $beside,
                ],
                'required' => ['api_version', 'kind', 'data'],
            ],
        ],
    ];
}

it('refuses a reference with a constraint beside it, and names where', function () use ($root): void {
    $constrained = contractPointingAt([
        'type' => 'object',
        'properties' => ['kind' => ['const' => 'word']],
    ]);
    $tree = treeWith($root, json_encode($constrained, JSON_THROW_ON_ERROR));

    $result = generateIn($tree);

    expect($result['status'])->toBe(1)
        ->and($result['stderr'])->toContain('/word/properties/data')
        ->and(is_dir($tree . '/src/Generated'))->toBeFalse();

    removeTree($tree);
});

it('accepts a reference described but not constrained', function () use ($root): void {
    $described = contractPointingAt(['description' => 'The payload.']);
    $tree = treeWith($root, json_encode($described, JSON_THROW_ON_ERROR));

    $result = generateIn($tree);

    expect($result['status'])->toBe(0)
        ->and(is_file($tree . '/src/Generated/Contract.php'))->toBeTrue();

    removeTree($tree);
});

/**
 * A contract whose one kind points at a definition that is somewhere else.
 *
 * Which is what hoisting `$defs` to the document root produces: the definition
 * is still in the artefact, and no longer where the kind pointing at it looks.
 *
 * @return array<string, mixed>
 */
function contractHoistedToTheRoot(): array
{
    return [
        'api_version' => 1,
        '$defs' => ['Word' => ['type' => 'object']],
        'kinds' => [
            'word' => [
                'type' => 'object',
                'properties' => [
                    'api_version' => ['type' => 'integer'],
                    'kind' => ['type' => 'string'],
                    'data' => ['$ref' => '#/$defs/Word'],
                ],
                'required' => ['api_version', 'kind', 'data'],
            ],
        ],
    ];
}

it('refuses a reference to a definition the kind does not carry, and names it', function () use ($root): void {
    $hoisted = contractHoistedToTheRoot();
    $tree = treeWith($root, json_encode($hoisted, JSON_THROW_ON_ERROR));

    $result = generateIn($tree);

    // Nought with a surface of `mixed` in it is the outcome this exists to stop,
    // so the status matters as much as the words.
    expect($result['status'])->toBe(1)
        ->and($result['stderr'])->toContain('word -> Word')
        ->and($result['stderr'])->toContain('mixed')
        ->and(is_dir($tree . '/src/Generated'))->toBeFalse();

    removeTree($tree);
});

it('accepts a reference to a definition the kind carries', function () use ($root): void {
    $carried = contractPointingAt([]);
    $tree = treeWith($root, json_encode($carried, JSON_THROW_ON_ERROR));

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

/**
 * A contract with one kind and the refusals given to it.
 *
 * @return array<string, mixed>
 */
function contractRefusing(mixed $refusals): array
{
    return contractOf(1) + ['refusals' => $refusals];
}

/**
 * What the generated refusal list answers when it is loaded and asked.
 *
 * Loaded in a process of its own, so the list a test generated is never the
 * one this suite is running against.
 */
function askTheRefusalList(string $tree, string $question): string
{
    $script = sprintf(
        'foreach (%s as $file) { require $file; } use Lemonfiber\\Sdk\\Generated\\RefusalCode; echo json_encode(%s);',
        var_export(array_map(fn(string $class): string => $tree . '/src/Generated/' . $class . '.php', ['RefusalCode', 'RefusalStatus', 'RefusalDescription']), true),
        $question,
    );

    return (string) shell_exec(escapeshellarg(PHP_BINARY) . ' -r ' . escapeshellarg($script));
}

it('writes a case per refusal the contract lists, in the order of its code', function () use ($root): void {
    $tree = treeWith($root, json_encode(contractRefusing([
        'ADMIT-10' => ['name' => 'NOT_A_PASSWORD', 'status' => 400, 'description' => 'Raised when what was offered is not a password.'],
        'ADMIT-4' => ['name' => 'NOT_ADMITTED', 'status' => 403, 'description' => "Raised when a request carried\n no token */ this run admits."],
    ]), JSON_THROW_ON_ERROR));

    $result = generateIn($tree);
    $source = (string) file_get_contents($tree . '/src/Generated/RefusalCode.php');

    expect($result['status'])->toBe(0)
        ->and($source)->toContain('enum RefusalCode: string')
        ->and($source)->toContain("case NotAdmitted = 'ADMIT-4';")
        ->and($source)->toContain("case NotAPassword = 'ADMIT-10';")
        ->and($source)->toMatch('/NotAdmitted = .*NotAPassword = /s')
        ->and((string) file_get_contents($tree . '/src/Generated/RefusalStatus.php'))->toContain('RefusalCode::NotAdmitted => 403,')
        ->and(askTheRefusalList($tree, 'array_map(fn($c) => [$c->name, $c->value, $c->status()], RefusalCode::cases())'))
        ->toBe('[["NotAdmitted","ADMIT-4",403],["NotAPassword","ADMIT-10",400]]')
        ->and(askTheRefusalList($tree, 'RefusalCode::NotAdmitted->description()'))
        ->toBe(json_encode("Raised when a request carried\n no token */ this run admits.", JSON_THROW_ON_ERROR))
        ->and(askTheRefusalList($tree, '[RefusalCode::of("ADMIT-4")?->name, RefusalCode::of("NOBODY-1"), RefusalCode::of(null)]'))
        ->toBe('["NotAdmitted",null,null]');

    removeTree($tree);
});

it('writes a list with no cases where the contract lists no refusals', function (array $contract) use ($root): void {
    $tree = treeWith($root, json_encode($contract, JSON_THROW_ON_ERROR));

    $result = generateIn($tree);

    expect($result['status'])->toBe(0)
        ->and((string) file_get_contents($tree . '/src/Generated/RefusalCode.php'))->not->toMatch('/^\s*case /m')
        ->and(askTheRefusalList($tree, '[RefusalCode::cases(), RefusalCode::of("ADMIT-4")]'))->toBe('[[],null]');

    removeTree($tree);
})->with([
    'the list left out' => [contractOf(1)],
    'the list empty' => [contractRefusing([])],
]);

it('refuses a malformed list of refusals, names what is wrong, and writes nothing', function (mixed $refusals, string $named) use ($root): void {
    $tree = treeWith($root, json_encode(contractRefusing($refusals), JSON_THROW_ON_ERROR));

    $result = generateIn($tree);

    expect($result['status'])->toBe(1)
        ->and($result['stderr'])->toContain($named)
        ->and($result['stderr'])->toContain('Nothing was generated.')
        ->and(is_dir($tree . '/src/Generated'))->toBeFalse();

    removeTree($tree);
})->with([
    'not an object' => ['ADMIT-4', 'object keyed by code'],
    'nothing at all' => [null, 'object keyed by code'],
    'a list rather than codes' => [[['name' => 'NOT_ADMITTED', 'status' => 403, 'description' => 'Raised.']], 'under `0`'],
    'an empty code' => [['' => ['name' => 'NOT_ADMITTED', 'status' => 403, 'description' => 'Raised.']], 'under ``'],
    'an entry that is not an object' => [['ADMIT-4' => 'NOT_ADMITTED'], '`ADMIT-4` is not an object'],
    'no name' => [['ADMIT-4' => ['status' => 403, 'description' => 'Raised.']], '`ADMIT-4` carries no name'],
    'a name in another case' => [['ADMIT-4' => ['name' => 'NotAdmitted', 'status' => 403, 'description' => 'Raised.']], 'SCREAMING_SNAKE'],
    'a name with an empty word' => [['ADMIT-4' => ['name' => 'NOT__ADMITTED', 'status' => 403, 'description' => 'Raised.']], 'SCREAMING_SNAKE'],
    'a name opening on a digit' => [['ADMIT-4' => ['name' => '4_NOT_ADMITTED', 'status' => 403, 'description' => 'Raised.']], 'SCREAMING_SNAKE'],
    'a name PHP will not take' => [['ADMIT-4' => ['name' => 'CLASS', 'status' => 403, 'description' => 'Raised.']], 'is named Class'],
    'no status' => [['ADMIT-4' => ['name' => 'NOT_ADMITTED', 'description' => 'Raised.']], '`ADMIT-4` carries no whole-number status'],
    'a status in words' => [['ADMIT-4' => ['name' => 'NOT_ADMITTED', 'status' => '403', 'description' => 'Raised.']], 'whole-number status'],
    'a status with a fraction' => [['ADMIT-4' => ['name' => 'NOT_ADMITTED', 'status' => 403.5, 'description' => 'Raised.']], 'whole-number status'],
    'a status nothing is refused with' => [['ADMIT-4' => ['name' => 'NOT_ADMITTED', 'status' => 200, 'description' => 'Raised.']], 'whole-number status'],
    'a status past the last' => [['ADMIT-4' => ['name' => 'NOT_ADMITTED', 'status' => 600, 'description' => 'Raised.']], 'whole-number status'],
    'no description' => [['ADMIT-4' => ['name' => 'NOT_ADMITTED', 'status' => 403]], '`ADMIT-4` carries no description'],
    'a description of nothing' => [['ADMIT-4' => ['name' => 'NOT_ADMITTED', 'status' => 403, 'description' => '  ']], 'no description'],
    'a description that is not text' => [['ADMIT-4' => ['name' => 'NOT_ADMITTED', 'status' => 403, 'description' => 7]], 'no description'],
    'two names that meet' => [[
        'ADMIT-4' => ['name' => 'AB_1', 'status' => 403, 'description' => 'Raised.'],
        'ADMIT-5' => ['name' => 'AB1', 'status' => 403, 'description' => 'Raised.'],
    ], '`ADMIT-4` and `ADMIT-5` would both be named Ab1'],
    'one name twice' => [[
        'ADMIT-4' => ['name' => 'NOT_ADMITTED', 'status' => 403, 'description' => 'Raised.'],
        'ADMIT-6' => ['name' => 'NOT_ADMITTED', 'status' => 403, 'description' => 'Raised.'],
    ], 'would both be named NotAdmitted'],
]);

it('writes nothing where a file would hold more lines than the cap, naming it', function () use ($root): void {
    $refusals = [];

    for ($at = 1; $at <= LineCap::MAX_LINES; ++$at) {
        $refusals['READ-' . $at] = ['name' => 'NUMBER_' . $at, 'status' => 404, 'description' => 'Raised.'];
    }

    $tree = treeWith($root, json_encode(contractRefusing($refusals), JSON_THROW_ON_ERROR));

    $result = generateIn($tree);

    expect($result['status'])->toBe(1)
        ->and($result['stderr'])->toContain('src/Generated/RefusalCode.php would hold ')
        ->and($result['stderr'])->toContain(sprintf('over the %d a file may hold', LineCap::MAX_LINES))
        ->and(is_dir($tree . '/src/Generated'))->toBeFalse();

    removeTree($tree);
});

it('accepts the edges of the statuses a refusal is answered with', function (int $status) use ($root): void {
    $tree = treeWith($root, json_encode(contractRefusing([
        'ADMIT-4' => ['name' => 'NOT_ADMITTED', 'status' => $status, 'description' => 'Raised.'],
    ]), JSON_THROW_ON_ERROR));

    expect(generateIn($tree)['status'])->toBe(0)
        ->and(askTheRefusalList($tree, 'RefusalCode::NotAdmitted->status()'))->toBe((string) $status);

    removeTree($tree);
})->with([400, 599]);

/**
 * What the generated list of actions a key may call answers when it is loaded
 * and asked, in a process of its own.
 */
function askTheKeyCallableList(string $tree, string $question): string
{
    $script = sprintf(
        'require %s; use Lemonfiber\\Sdk\\Generated\\KeyCallableAction; echo json_encode(%s);',
        var_export($tree . '/src/Generated/KeyCallableAction.php', true),
        $question,
    );

    return (string) shell_exec(escapeshellarg(PHP_BINARY) . ' -r ' . escapeshellarg($script));
}

it('writes a case per action a key may call, in the contract\'s order, with what it says of each', function () use ($root): void {
    $tree = treeWith($root, json_encode(contractOf(1) + ['key_callable' => [
        ['action' => 'restart', 'disturbs' => true, 'rehearsal' => true],
        ['action' => 'diagnose', 'disturbs' => true, 'rehearsal' => false],
        ['action' => 'downloads-pause', 'disturbs' => false, 'rehearsal' => true],
    ]], JSON_THROW_ON_ERROR));

    expect(generateIn($tree)['status'])->toBe(0)
        ->and(askTheKeyCallableList($tree, 'array_map(fn($a) => [$a->name, $a->value, $a->disturbs(), $a->rehearsal()], KeyCallableAction::cases())'))
        ->toBe('[["Restart","restart",true,true],["Diagnose","diagnose",true,false],["DownloadsPause","downloads-pause",false,true]]')
        ->and(askTheKeyCallableList($tree, '[KeyCallableAction::of("diagnose")?->name, KeyCallableAction::of("uninstall")]'))
        ->toBe('["Diagnose",null]');

    removeTree($tree);
});

it('writes a list with no cases where the contract lists no action a key may call', function (array $contract) use ($root): void {
    $tree = treeWith($root, json_encode($contract, JSON_THROW_ON_ERROR));

    expect(generateIn($tree)['status'])->toBe(0)
        ->and(askTheKeyCallableList($tree, '[KeyCallableAction::cases(), KeyCallableAction::of("restart")]'))->toBe('[[],null]');

    removeTree($tree);
})->with([
    'the list left out' => [contractOf(1)],
    'the list empty' => [contractOf(1) + ['key_callable' => []]],
]);

it('refuses a malformed list of actions a key may call, names what is wrong, and writes nothing', function (mixed $listed, string $named) use ($root): void {
    $tree = treeWith($root, json_encode(contractOf(1) + ['key_callable' => $listed], JSON_THROW_ON_ERROR));

    $result = generateIn($tree);

    expect($result['status'])->toBe(1)
        ->and($result['stderr'])->toContain($named)
        ->and($result['stderr'])->toContain('Nothing was generated.')
        ->and(is_dir($tree . '/src/Generated'))->toBeFalse();

    removeTree($tree);
})->with([
    'not a list' => [['restart' => ['disturbs' => true, 'rehearsal' => true]], 'something other than a list'],
    'nothing at all' => [null, 'something other than a list'],
    'an entry that is not an object' => [['restart'], 'Entry 0 of the actions a key may call is not an object'],
    'no action' => [[['disturbs' => true, 'rehearsal' => true]], 'Entry 0 of the actions a key may call names no action'],
    'an action in another case' => [[['action' => 'Restart', 'disturbs' => true, 'rehearsal' => true]], 'names no action'],
    'an action with an empty word' => [[['action' => 'downloads--pause', 'disturbs' => true, 'rehearsal' => true]], 'names no action'],
    'no disturbs' => [[['action' => 'restart', 'rehearsal' => true]], '`restart` does not say both'],
    'a rehearsal in words' => [[['action' => 'restart', 'disturbs' => true, 'rehearsal' => 'yes']], '`restart` does not say both'],
    'one action twice' => [[
        ['action' => 'restart', 'disturbs' => true, 'rehearsal' => true],
        ['action' => 'restart', 'disturbs' => false, 'rehearsal' => true],
    ], '`restart` is listed twice'],
]);
