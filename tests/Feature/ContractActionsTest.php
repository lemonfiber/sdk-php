<?php

declare(strict_types=1);

use Lemonfiber\Sdk\Tests\Support\ContractTree;

/**
 * The generator writing a class per action the contract lists, and refusing a
 * list it cannot write as classes that tell the truth.
 */

/**
 * A contract with one kind and the actions given, keyed by name.
 *
 * @param  array<mixed, mixed>  $actions
 * @return array<string, mixed>
 */
function contractActing(array $actions): array
{
    return [
        'api_version' => 1,
        'actions' => $actions,
        'kinds' => ['word' => aWordKind()],
    ];
}

/**
 * The one kind every contract here describes.
 *
 * @return array<string, mixed>
 */
function aWordKind(): array
{
    return [
        'type' => 'object',
        'properties' => [
            'api_version' => ['type' => 'integer'],
            'kind' => ['type' => 'string'],
            'data' => ['type' => 'object'],
        ],
        'required' => ['api_version', 'kind', 'data'],
    ];
}

/**
 * An action as the contract describes one.
 *
 * @param  list<array{name: string, type: array<string, mixed>}>  $arguments
 * @param  list<string>  $consent
 * @return array<string, mixed>
 */
function anAction(string $name, array $arguments = [], array $consent = [], bool $rehearsal = true): array
{
    return ['action' => $name, 'arguments' => $arguments, 'consent' => $consent, 'rehearsal' => $rehearsal];
}

/**
 * What a generated action answers, asked in a process of its own that loads this package and the tree's classes.
 */
function askTheActions(string $tree, string $question): string
{
    $script = sprintf(
        'require %s; foreach (glob(%s) as $f) { require_once $f; } echo json_encode(%s);',
        var_export(dirname(__DIR__, 2) . '/vendor/autoload.php', true),
        var_export($tree . '/src/Generated/*Action.php', true),
        $question,
    );

    return (string) shell_exec(escapeshellarg(PHP_BINARY) . ' -r ' . escapeshellarg($script) . ' 2>&1');
}

/** @return array<string, mixed> */
function aList(): array
{
    return ['default' => [], 'description' => 'The forms to act on.', 'items' => ['type' => 'string'], 'type' => 'array'];
}

it('writes a class per action, taking each argument by name with its type and default', function (): void {
    $tree = ContractTree::withFile(json_encode(contractActing([
        'stop-seeding' => anAction('stop-seeding', [
            ['name' => 'forms', 'type' => aList()],
            ['name' => 'age_limit', 'type' => ['default' => null, 'minimum' => 0, 'type' => ['integer', 'null']]],
            ['name' => 'confirm', 'type' => ['default' => false, 'type' => 'boolean']],
        ], ['confirm']),
    ]), JSON_THROW_ON_ERROR));

    $result = ContractTree::generate($tree);
    $source = (string) file_get_contents($tree . '/src/Generated/StopSeedingAction.php');

    expect($result['status'])->toBe(0)
        ->and($source)->toContain('final class StopSeedingAction extends ActionRequest')
        ->and($source)->toContain('public readonly array $forms = [],')
        ->and($source)->toContain('public readonly ?int $ageLimit = null,')
        ->and($source)->toContain('@param  int<0, max>|null  $ageLimit')
        ->and($source)->toContain("@param  bool  \$confirm  Carries the operator's yes.")
        ->and(askTheActions($tree, 'new Lemonfiber\Sdk\Generated\StopSeedingAction(forms: ["tv"], ageLimit: 12)->payload()'))
        ->toBe('{"forms":["tv"],"age_limit":12,"confirm":false}')
        ->and(askTheActions($tree, '[new Lemonfiber\Sdk\Generated\StopSeedingAction()->endpoint(), new Lemonfiber\Sdk\Generated\StopSeedingAction()->consent()]'))
        ->toBe('["\/api\/actions\/stop-seeding",["confirm"]]');

    ContractTree::remove($tree);
});

it('asks for the arguments that have no default before those that do', function (): void {
    $tree = ContractTree::withFile(json_encode(contractActing([
        'down' => anAction('down', [
            ['name' => 'forms', 'type' => aList()],
            ['name' => 'wait', 'type' => ['type' => 'boolean']],
        ]),
    ]), JSON_THROW_ON_ERROR));

    expect(ContractTree::generate($tree)['status'])->toBe(0)
        ->and((string) file_get_contents($tree . '/src/Generated/DownAction.php'))
        ->toContain("        public readonly bool \$wait,\n        public readonly array \$forms = [],")
        ->and(askTheActions($tree, 'new Lemonfiber\Sdk\Generated\DownAction(true)->payload()'))
        ->toBe('{"forms":[],"wait":true}');

    ContractTree::remove($tree);
});

it('offers a rehearsal only where the action takes one, and rehearses a copy', function (): void {
    $tree = ContractTree::withFile(json_encode(contractActing([
        'restart' => anAction('restart', [['name' => 'forms', 'type' => aList()]]),
        'accept' => anAction('accept', rehearsal: false),
    ]), JSON_THROW_ON_ERROR));

    $question = '[($a = new Lemonfiber\Sdk\Generated\RestartAction(forms: ["tv"]))->rehearsed()->payload(), $a->payload(), '
        . 'method_exists(Lemonfiber\Sdk\Generated\AcceptAction::class, "rehearsed")]';

    expect(ContractTree::generate($tree)['status'])->toBe(0)
        ->and(askTheActions($tree, $question))->toBe('[{"forms":["tv"],"dry_run":true},{"forms":["tv"]},false]');

    ContractTree::remove($tree);
});

it('writes no action where the contract lists none', function (): void {
    $tree = ContractTree::withFile(json_encode(contractActing([]), JSON_THROW_ON_ERROR));

    expect(ContractTree::generate($tree)['status'])->toBe(0)
        ->and(glob($tree . '/src/Generated/*Action.php'))->toBe([$tree . '/src/Generated/KeyCallableAction.php']);

    ContractTree::remove($tree);
});

it('refuses a list of actions it cannot write as classes, names what is wrong, and writes nothing', function (mixed $actions, string $named): void {
    $tree = ContractTree::withFile(json_encode(['actions' => $actions] + contractActing([]), JSON_THROW_ON_ERROR));

    $result = ContractTree::generate($tree);

    expect($result['status'])->toBe(1)
        ->and($result['stderr'])->toContain($named)
        ->and($result['stderr'])->toContain('Nothing was generated.')
        ->and(is_dir($tree . '/src/Generated'))->toBeFalse();

    ContractTree::remove($tree);
})->with([
    'a list rather than a map' => [[anAction('restart')], 'something other than a map'],
    'a name no action has' => [['Restart' => anAction('Restart')], 'which is no action name'],
    'an entry describing another' => [['restart' => anAction('up')], '`restart` is not described as itself'],
    'no word on rehearsal' => [['restart' => ['action' => 'restart', 'arguments' => [], 'consent' => []]], 'whether it can be rehearsed'],
    'arguments not a list' => [['restart' => ['action' => 'restart', 'arguments' => 'forms', 'consent' => [], 'rehearsal' => true]], 'lists its arguments as something other than a list'],
    'an argument twice' => [['restart' => anAction('restart', [['name' => 'forms', 'type' => aList()], ['name' => 'forms', 'type' => aList()]])], 'lists its argument `forms` twice'],
    'a default of another type' => [['support' => anAction('support', [['name' => 'filenames', 'type' => ['default' => 'Replaced', 'type' => 'boolean']]])], 'its argument `filenames` the default "Replaced", which is not of its type'],
    'a list item of another type' => [['restart' => anAction('restart', [['name' => 'forms', 'type' => ['default' => [1], 'items' => ['type' => 'string'], 'type' => 'array']]])], 'the default [1]'],
    'a constraint a parameter cannot hold' => [['quality-set' => anAction('quality-set', [['name' => 'preset', 'type' => ['enum' => ['hd'], 'type' => 'string']]])], 'with `enum`'],
    'two types' => [['set' => anAction('set', [['name' => 'value', 'type' => ['type' => ['string', 'integer']]]])], 'which is not one type a parameter can be'],
    'a list of objects' => [['set' => anAction('set', [['name' => 'value', 'type' => ['items' => ['type' => 'object'], 'type' => 'array']]])], 'a list of something other than one plain type'],
    'consent in an argument it does not take' => [['forget' => anAction('forget', consent: ['confirm'])], 'says consent travels in `confirm`, which it does not take'],
    'two actions written as one class' => [['ab-c' => anAction('ab-c'), 'a-bc' => anAction('a-bc')], 'would be written as ABcAction, as another action already is'],
    'a class this already writes' => [['key-callable' => anAction('key-callable')], 'KeyCallableAction, a class this already writes'],
]);

it('reads the actions the directory lists, each from its own file', function (): void {
    $tree = ContractTree::withDirectory([
        'index.json' => ['api_version' => 1, 'actions' => ['restart' => 'actions/restart.json'], 'kinds' => ['word' => 'kinds/word.json']],
        'actions/restart.json' => anAction('restart', [['name' => 'forms', 'type' => aList()]]),
        'kinds/word.json' => aWordKind(),
    ]);

    expect(ContractTree::generate($tree)['status'])->toBe(0)
        ->and(askTheActions($tree, 'new Lemonfiber\Sdk\Generated\RestartAction(forms: ["tv"])->payload()'))->toBe('{"forms":["tv"]}');

    ContractTree::remove($tree);
});
