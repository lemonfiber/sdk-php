<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Scripts;

use function array_is_list;
use function array_key_exists;
use function is_array;
use function is_string;
use function sprintf;

use UnexpectedValueException;

/**
 * What a member's surface may not show, as a contract artefact publishes it, read and checked.
 *
 * Each list is words in the contract's order, each declared name is keyed to what it names,
 * and each example is a text with what in it a member may not be shown. An artefact
 * publishing none publishes empty lists.
 *
 * @phpstan-type Words array{names: list<string>, terms: list<string>, sizes: list<string>, marks: list<string>, declared: array<string, string>, examples: list<array{text: string, names: string}>}
 */
final readonly class HouseholdWords
{
    public const string KEY = 'household_words';

    /**
     * The lists of words, each read under the same key.
     *
     * @var list<'names'|'terms'|'sizes'|'marks'>
     */
    public const array LISTS = ['names', 'terms', 'sizes', 'marks'];

    private const string DECLARED = 'declared';

    private const string EXAMPLES = 'examples';

    /**
     * Every list the artefact publishes, empty where it publishes none.
     *
     * @param  array<mixed, mixed>  $artefact
     * @return Words
     *
     * @throws UnexpectedValueException naming what is wrong, where the words are malformed
     */
    public function listed(array $artefact): array
    {
        $words = ['names' => [], 'terms' => [], 'sizes' => [], 'marks' => [], 'declared' => [], 'examples' => []];

        if (! array_key_exists(self::KEY, $artefact)) {
            return $words;
        }

        $published = $artefact[self::KEY];

        if (! is_array($published) || ($published !== [] && array_is_list($published))) {
            throw new UnexpectedValueException('The vendored contract publishes what a member may not be shown as something other than an object.');
        }

        foreach (self::LISTS as $list) {
            $words[$list] = $this->strings($published[$list] ?? null, $list);
        }

        $words[self::DECLARED] = $this->declared($published[self::DECLARED] ?? null);
        $words[self::EXAMPLES] = $this->examples($published[self::EXAMPLES] ?? null);

        return $words;
    }

    /**
     * @return list<string>
     *
     * @throws UnexpectedValueException
     */
    private function strings(mixed $listed, string $list): array
    {
        if (! is_array($listed) || ! array_is_list($listed)) {
            throw new UnexpectedValueException(sprintf('The vendored contract publishes no list of `%s` a member may not be shown.', $list));
        }

        $read = [];

        foreach ($listed as $at => $word) {
            if (! is_string($word) || $word === '') {
                throw new UnexpectedValueException(sprintf('Entry %d of the `%s` a member may not be shown is not a word.', $at, $list));
            }

            $read[] = $word;
        }

        return $read;
    }

    /**
     * @return array<string, string>
     *
     * @throws UnexpectedValueException
     */
    private function declared(mixed $published): array
    {
        if (! is_array($published) || ($published !== [] && array_is_list($published))) {
            throw new UnexpectedValueException('The vendored contract publishes the names lemonfiber declares as something other than an object.');
        }

        $read = [];

        foreach ($published as $name => $names) {
            if (! is_string($name) || $name === '' || ! is_string($names) || $names === '') {
                throw new UnexpectedValueException('A name lemonfiber declares is published without what it names.');
            }

            $read[$name] = $names;
        }

        return $read;
    }

    /**
     * @return list<array{text: string, names: string}>
     *
     * @throws UnexpectedValueException
     */
    private function examples(mixed $listed): array
    {
        if (! is_array($listed) || ! array_is_list($listed)) {
            throw new UnexpectedValueException('The vendored contract publishes no list of examples a member may not be shown.');
        }

        $read = [];

        foreach ($listed as $at => $example) {
            $text = is_array($example) ? ($example['text'] ?? null) : null;
            $names = is_array($example) ? ($example['names'] ?? null) : null;

            if (! is_string($text) || ! is_string($names) || $text === '' || $names === '') {
                throw new UnexpectedValueException(sprintf('Example %d of what a member may not be shown is not a text with what it names.', $at));
            }

            $read[] = ['text' => $text, 'names' => $names];
        }

        return $read;
    }
}
