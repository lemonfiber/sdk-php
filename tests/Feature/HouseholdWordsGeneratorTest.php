<?php

declare(strict_types=1);

use Lemonfiber\Sdk\Tests\Support\ContractTree;

/**
 * What a member's surface may not show, as the generator writes it from the contract.
 */

/**
 * A contract with one kind, carrying `$words` as what a member may not be shown where any are given.
 *
 * @return array<string, mixed>
 */
function contractWithWords(mixed $words = null): array
{
    $contract = [
        'api_version' => 1,
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

    return $words === null ? $contract : $contract + ['household_words' => $words];
}

/**
 * Words as the core publishes them.
 *
 * @return array<string, mixed>
 */
function publishedWords(): array
{
    return [
        'names' => ['jellyfin', 'flare solverr'],
        'terms' => ['indexer'],
        'sizes' => ['gb'],
        'marks' => ['://', "it's"],
        'declared' => ['form' => 'names a combination of profiles', 'stack' => 'names the machine'],
        'examples' => [['text' => 'It needs 4 GB more', 'names' => '4']],
    ];
}

/**
 * What the generated words answer when loaded and asked, in a process of their own.
 */
function askTheWords(string $tree, string $question): string
{
    $script = sprintf(
        'require %s; use Lemonfiber\\Sdk\\Generated\\HouseholdWords; echo json_encode(%s);',
        var_export($tree . '/src/Generated/HouseholdWords.php', true),
        $question,
    );

    return (string) shell_exec(escapeshellarg(PHP_BINARY) . ' -r ' . escapeshellarg($script));
}

it('writes every list, the declared names and the examples the contract publishes, in its order', function (): void {
    $tree = ContractTree::withFile(json_encode(contractWithWords(publishedWords()), JSON_THROW_ON_ERROR));

    expect(ContractTree::generate($tree)['status'])->toBe(0)
        ->and(askTheWords($tree, '[HouseholdWords::NAMES, HouseholdWords::TERMS, HouseholdWords::SIZES, HouseholdWords::MARKS]'))
        ->toBe('[["jellyfin","flare solverr"],["indexer"],["gb"],[":\/\/","it\'s"]]')
        ->and(askTheWords($tree, '[HouseholdWords::DECLARED, HouseholdWords::EXAMPLES]'))
        ->toBe('[{"form":"names a combination of profiles","stack":"names the machine"},[{"text":"It needs 4 GB more","names":"4"}]]');

    ContractTree::remove($tree);
});

it('writes empty lists where the contract publishes no words', function (array $contract): void {
    $tree = ContractTree::withFile(json_encode($contract, JSON_THROW_ON_ERROR));

    expect(ContractTree::generate($tree)['status'])->toBe(0)
        ->and(askTheWords($tree, '[HouseholdWords::NAMES, HouseholdWords::MARKS, HouseholdWords::DECLARED, HouseholdWords::EXAMPLES]'))
        ->toBe('[[],[],[],[]]');

    ContractTree::remove($tree);
})->with([
    'the words left out' => [contractWithWords()],
    'every list empty' => [contractWithWords(['names' => [], 'terms' => [], 'sizes' => [], 'marks' => [], 'declared' => (object) [], 'examples' => []])],
]);

it('refuses malformed words, names what is wrong, and writes nothing', function (mixed $words, string $named): void {
    $tree = ContractTree::withFile(json_encode(contractWithWords($words), JSON_THROW_ON_ERROR));

    $result = ContractTree::generate($tree);

    expect($result['status'])->toBe(1)
        ->and($result['stderr'])->toContain($named)
        ->and($result['stderr'])->toContain('Nothing was generated.')
        ->and(is_dir($tree . '/src/Generated'))->toBeFalse();

    ContractTree::remove($tree);
})->with([
    'a list rather than an object' => [['jellyfin'], 'something other than an object'],
    'no marks' => [array_diff_key(publishedWords(), ['marks' => true]), 'no list of `marks`'],
    'a name that is not a word' => [['names' => [''], 'marks' => []] + publishedWords(), 'Entry 0 of the `names`'],
    'a term that is a number' => [['terms' => [4]] + publishedWords(), 'Entry 0 of the `terms`'],
    'declared names as a list' => [['declared' => ['form']] + publishedWords(), 'names lemonfiber declares as something other than an object'],
    'a declared name saying nothing' => [['declared' => ['form' => '']] + publishedWords(), 'without what it names'],
    'no examples' => [array_diff_key(publishedWords(), ['examples' => true]), 'no list of examples'],
    'an example without what it names' => [['examples' => [['text' => 'It needs 4 GB more']]] + publishedWords(), 'Example 0'],
]);
