<?php

declare(strict_types=1);

use Lemonfiber\Sdk\Scripts\LineCap;
use Lemonfiber\Sdk\Tests\Support\ContractTree;

/**
 * The generator's refusals, exercised as the command it is, in a tree of its own.
 */

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

it('refuses a version it does not implement, and names both', function (): void {
    $tree = ContractTree::withFile(json_encode(contractOf(2), JSON_THROW_ON_ERROR));

    $result = ContractTree::generate($tree);

    expect($result['status'])->toBe(1)
        ->and($result['stderr'])->toContain('2')
        ->and($result['stderr'])->toContain('1');

    ContractTree::remove($tree);
});

it('writes nothing when it refuses', function (): void {
    $tree = ContractTree::withFile(json_encode(contractOf(2), JSON_THROW_ON_ERROR));

    ContractTree::generate($tree);

    expect(is_dir($tree . '/src/Generated'))->toBeFalse();

    ContractTree::remove($tree);
});

it('writes when the version is the one it implements', function (): void {
    $tree = ContractTree::withFile(json_encode(contractOf(1), JSON_THROW_ON_ERROR));

    $result = ContractTree::generate($tree);

    expect($result['status'])->toBe(0)
        ->and(is_file($tree . '/src/Generated/Contract.php'))->toBeTrue();

    ContractTree::remove($tree);
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

it('refuses a reference with a constraint beside it, and names where', function (): void {
    $constrained = contractPointingAt([
        'type' => 'object',
        'properties' => ['kind' => ['const' => 'word']],
    ]);
    $tree = ContractTree::withFile(json_encode($constrained, JSON_THROW_ON_ERROR));

    $result = ContractTree::generate($tree);

    expect($result['status'])->toBe(1)
        ->and($result['stderr'])->toContain('/word/properties/data')
        ->and(is_dir($tree . '/src/Generated'))->toBeFalse();

    ContractTree::remove($tree);
});

it('accepts a reference described but not constrained', function (): void {
    $described = contractPointingAt(['description' => 'The payload.']);
    $tree = ContractTree::withFile(json_encode($described, JSON_THROW_ON_ERROR));

    $result = ContractTree::generate($tree);

    expect($result['status'])->toBe(0)
        ->and(is_file($tree . '/src/Generated/Contract.php'))->toBeTrue();

    ContractTree::remove($tree);
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

it('refuses a reference to a definition the kind does not carry, and names it', function (): void {
    $hoisted = contractHoistedToTheRoot();
    $tree = ContractTree::withFile(json_encode($hoisted, JSON_THROW_ON_ERROR));

    $result = ContractTree::generate($tree);

    // Nought with a surface of `mixed` in it is the outcome this exists to stop,
    // so the status matters as much as the words.
    expect($result['status'])->toBe(1)
        ->and($result['stderr'])->toContain('contract/web-api.contract.json')
        ->and($result['stderr'])->toContain('kind `word` -> "#/$defs/Word"')
        ->and($result['stderr'])->toContain('mixed')
        ->and($result['stderr'])->toContain('Nothing was generated.')
        ->and(is_dir($tree . '/src/Generated'))->toBeFalse();

    ContractTree::remove($tree);
});

/**
 * A contract whose one kind carries the definitions given and describes its payload as given.
 *
 * @param  array<string, mixed>  $data
 * @param  array<string, mixed>  $defs
 * @return array<string, mixed>
 */
function contractCarrying(array $data, array $defs): array
{
    return [
        'api_version' => 1,
        'kinds' => [
            'word' => [
                'type' => 'object',
                '$defs' => $defs,
                'properties' => [
                    'api_version' => ['type' => 'integer'],
                    'kind' => ['type' => 'string'],
                    'data' => $data,
                ],
                'required' => ['api_version', 'kind', 'data'],
            ],
        ],
    ];
}

/**
 * A contract whose one kind carries `Word` and describes its payload with the reference given.
 *
 * @return array<string, mixed>
 */
function contractReferring(mixed $reference): array
{
    return contractCarrying(['$ref' => $reference], ['Word' => ['type' => 'object']]);
}

it('refuses a reference it cannot resolve to a definition, names it and the file, and writes nothing', function (mixed $reference, string $named): void {
    $tree = ContractTree::withFile(json_encode(contractReferring($reference), JSON_THROW_ON_ERROR));

    $result = ContractTree::generate($tree);

    expect($result['status'])->toBe(1)
        ->and($result['stderr'])->toContain('contract/web-api.contract.json')
        ->and($result['stderr'])->toContain('kind `word` -> ' . $named)
        ->and($result['stderr'])->toContain('Nothing was generated.')
        ->and(is_dir($tree . '/src/Generated'))->toBeFalse();

    ContractTree::remove($tree);
})->with([
    'a path to a file' => ['../defs/Word.json', '"../defs/Word.json"'],
    'a pointer under another keyword' => ['#/definitions/Word', '"#/definitions/Word"'],
    'a pointer into another document' => ['other.json#/$defs/Word', '"other.json#/$defs/Word"'],
    'a definition not carried' => ['#/$defs/Phrase', '"#/$defs/Phrase"'],
    'the definitions themselves' => ['#/$defs/', '"#/$defs/"'],
    'a reference that is not text' => [7, '7'],
]);

it('refuses an unresolvable reference no payload reaches', function (): void {
    $contract = contractCarrying(['$ref' => '#/$defs/Word'], [
        'Word' => ['type' => 'object'],
        'Unused' => ['$ref' => '#/$defs/Gone'],
    ]);
    $tree = ContractTree::withFile(json_encode($contract, JSON_THROW_ON_ERROR));

    $result = ContractTree::generate($tree);

    expect($result['status'])->toBe(1)
        ->and($result['stderr'])->toContain('kind `word` -> "#/$defs/Gone"')
        ->and(is_dir($tree . '/src/Generated'))->toBeFalse();

    ContractTree::remove($tree);
});

it('writes a definition that holds itself, typing the recursion as mixed', function (): void {
    $contract = contractCarrying(['$ref' => '#/$defs/Word'], [
        'Word' => ['type' => 'object', 'properties' => ['inner' => ['$ref' => '#/$defs/Word']]],
    ]);
    $tree = ContractTree::withFile(json_encode($contract, JSON_THROW_ON_ERROR));

    $result = ContractTree::generate($tree);

    expect($result['status'])->toBe(0)
        ->and((string) file_get_contents($tree . '/src/Generated/WordEnvelope.php'))->toContain('@phpstan-type Word array{inner?: mixed}');

    ContractTree::remove($tree);
});

it('accepts a reference to a definition the kind carries', function (): void {
    $carried = contractPointingAt([]);
    $tree = ContractTree::withFile(json_encode($carried, JSON_THROW_ON_ERROR));

    $result = ContractTree::generate($tree);

    expect($result['status'])->toBe(0)
        ->and(is_file($tree . '/src/Generated/Contract.php'))->toBeTrue();

    ContractTree::remove($tree);
});

it('refuses a contract describing no kinds', function (): void {
    $tree = ContractTree::withFile(json_encode(['api_version' => 1, 'kinds' => []], JSON_THROW_ON_ERROR));

    $result = ContractTree::generate($tree);

    expect($result['status'])->toBe(1)
        ->and(is_dir($tree . '/src/Generated'))->toBeFalse();

    ContractTree::remove($tree);
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

it('writes a case per refusal the contract lists, in the order of its code', function (): void {
    $tree = ContractTree::withFile(json_encode(contractRefusing([
        'ADMIT-10' => ['name' => 'NOT_A_PASSWORD', 'status' => 400, 'description' => 'Raised when what was offered is not a password.'],
        'ADMIT-4' => ['name' => 'NOT_ADMITTED', 'status' => 403, 'description' => "Raised when a request carried\n no token */ this run admits."],
    ]), JSON_THROW_ON_ERROR));

    $result = ContractTree::generate($tree);
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

    ContractTree::remove($tree);
});

it('writes a list with no cases where the contract lists no refusals', function (array $contract): void {
    $tree = ContractTree::withFile(json_encode($contract, JSON_THROW_ON_ERROR));

    $result = ContractTree::generate($tree);

    expect($result['status'])->toBe(0)
        ->and((string) file_get_contents($tree . '/src/Generated/RefusalCode.php'))->not->toMatch('/^\s*case /m')
        ->and(askTheRefusalList($tree, '[RefusalCode::cases(), RefusalCode::of("ADMIT-4")]'))->toBe('[[],null]');

    ContractTree::remove($tree);
})->with([
    'the list left out' => [contractOf(1)],
    'the list empty' => [contractRefusing([])],
]);

it('refuses a malformed list of refusals, names what is wrong, and writes nothing', function (mixed $refusals, string $named): void {
    $tree = ContractTree::withFile(json_encode(contractRefusing($refusals), JSON_THROW_ON_ERROR));

    $result = ContractTree::generate($tree);

    expect($result['status'])->toBe(1)
        ->and($result['stderr'])->toContain($named)
        ->and($result['stderr'])->toContain('Nothing was generated.')
        ->and(is_dir($tree . '/src/Generated'))->toBeFalse();

    ContractTree::remove($tree);
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

it('writes nothing where a file would hold more lines than the cap, naming it', function (): void {
    $refusals = [];

    for ($at = 1; $at <= LineCap::MAX_LINES; ++$at) {
        $refusals['READ-' . $at] = ['name' => 'NUMBER_' . $at, 'status' => 404, 'description' => 'Raised.'];
    }

    $tree = ContractTree::withFile(json_encode(contractRefusing($refusals), JSON_THROW_ON_ERROR));

    $result = ContractTree::generate($tree);

    expect($result['status'])->toBe(1)
        ->and($result['stderr'])->toContain('src/Generated/RefusalCode.php would hold ')
        ->and($result['stderr'])->toContain(sprintf('over the %d a file may hold', LineCap::MAX_LINES))
        ->and(is_dir($tree . '/src/Generated'))->toBeFalse();

    ContractTree::remove($tree);
});

it('accepts the edges of the statuses a refusal is answered with', function (int $status): void {
    $tree = ContractTree::withFile(json_encode(contractRefusing([
        'ADMIT-4' => ['name' => 'NOT_ADMITTED', 'status' => $status, 'description' => 'Raised.'],
    ]), JSON_THROW_ON_ERROR));

    expect(ContractTree::generate($tree)['status'])->toBe(0)
        ->and(askTheRefusalList($tree, 'RefusalCode::NotAdmitted->status()'))->toBe((string) $status);

    ContractTree::remove($tree);
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

it('writes a case per action a key may call, in the contract\'s order, with what it says of each', function (): void {
    $tree = ContractTree::withFile(json_encode(contractOf(1) + ['key_callable' => [
        ['action' => 'restart', 'disturbs' => true, 'rehearsal' => true],
        ['action' => 'diagnose', 'disturbs' => true, 'rehearsal' => false],
        ['action' => 'downloads-pause', 'disturbs' => false, 'rehearsal' => true],
    ]], JSON_THROW_ON_ERROR));

    expect(ContractTree::generate($tree)['status'])->toBe(0)
        ->and(askTheKeyCallableList($tree, 'array_map(fn($a) => [$a->name, $a->value, $a->disturbs(), $a->rehearsal()], KeyCallableAction::cases())'))
        ->toBe('[["Restart","restart",true,true],["Diagnose","diagnose",true,false],["DownloadsPause","downloads-pause",false,true]]')
        ->and(askTheKeyCallableList($tree, '[KeyCallableAction::of("diagnose")?->name, KeyCallableAction::of("uninstall")]'))
        ->toBe('["Diagnose",null]');

    ContractTree::remove($tree);
});

it('writes a list with no cases where the contract lists no action a key may call', function (array $contract): void {
    $tree = ContractTree::withFile(json_encode($contract, JSON_THROW_ON_ERROR));

    expect(ContractTree::generate($tree)['status'])->toBe(0)
        ->and(askTheKeyCallableList($tree, '[KeyCallableAction::cases(), KeyCallableAction::of("restart")]'))->toBe('[[],null]');

    ContractTree::remove($tree);
})->with([
    'the list left out' => [contractOf(1)],
    'the list empty' => [contractOf(1) + ['key_callable' => []]],
]);

it('refuses a malformed list of actions a key may call, names what is wrong, and writes nothing', function (mixed $listed, string $named): void {
    $tree = ContractTree::withFile(json_encode(contractOf(1) + ['key_callable' => $listed], JSON_THROW_ON_ERROR));

    $result = ContractTree::generate($tree);

    expect($result['status'])->toBe(1)
        ->and($result['stderr'])->toContain($named)
        ->and($result['stderr'])->toContain('Nothing was generated.')
        ->and(is_dir($tree . '/src/Generated'))->toBeFalse();

    ContractTree::remove($tree);
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

/**
 * A contract whose two kinds carry the definition `$name` as their payload.
 *
 * @return array<string, mixed>
 */
function contractSharing(string $name): array
{
    $kind = static fn(): array => [
        'type' => 'object',
        '$defs' => [$name => ['type' => 'object', 'properties' => ['word' => ['type' => 'string']]]],
        'properties' => [
            'api_version' => ['type' => 'integer'],
            'kind' => ['type' => 'string'],
            'data' => ['$ref' => '#/$defs/' . $name],
        ],
        'required' => ['api_version', 'kind', 'data'],
    ];

    return ['api_version' => 1, 'kinds' => ['word' => $kind(), 'phrase' => $kind()]];
}

it('names a shape two kinds carry once, and imports it into both', function (): void {
    $tree = ContractTree::withFile(json_encode(contractSharing('Said'), JSON_THROW_ON_ERROR));

    $result = ContractTree::generate($tree);
    $shapes = (string) file_get_contents($tree . '/src/Generated/Shapes.php');
    $word = (string) file_get_contents($tree . '/src/Generated/WordEnvelope.php');
    $phrase = (string) file_get_contents($tree . '/src/Generated/PhraseEnvelope.php');

    expect($result['status'])->toBe(0)
        ->and($shapes)->toContain('@phpstan-type Said array{word?: string}')
        ->and($word)->toContain('@phpstan-import-type Said from Shapes')
        ->and($word)->toContain('@phpstan-type Data Said')
        ->and($phrase)->toContain('@phpstan-import-type Said from Shapes');

    ContractTree::remove($tree);
});

it('refuses a definition named like a generated class, names it, and writes nothing', function (): void {
    $tree = ContractTree::withFile(json_encode(contractSharing('Kind'), JSON_THROW_ON_ERROR));

    $result = ContractTree::generate($tree);

    expect($result['status'])->toBe(1)
        ->and($result['stderr'])->toContain('`Kind`')
        ->and(is_dir($tree . '/src/Generated'))->toBeFalse();

    ContractTree::remove($tree);
});
