<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Scripts;

use function array_key_exists;
use function explode;
use function is_array;
use function is_int;
use function is_string;
use function ksort;
use function preg_match;
use function sprintf;
use function strtolower;
use function trim;
use function ucfirst;

use UnexpectedValueException;

/**
 * The refusal codes a contract artefact lists, read and checked.
 *
 * The list is keyed by code. Each entry names the code as the core's registry
 * does, in SCREAMING_SNAKE, and gives the one status the refusal is answered
 * with and the registry's sentence about it. An artefact listing none lists an
 * empty set.
 */
final readonly class Refusals
{
    private const string KEY = 'refusals';

    private const string SCREAMING_SNAKE = '/^[A-Z][A-Z0-9]*(?:_[A-Z0-9]+)*$/';

    /**
     * The lowest status a refusal is answered with.
     */
    private const int LOWEST = 400;

    /**
     * The highest status a refusal is answered with.
     */
    private const int HIGHEST = 599;

    /**
     * Every refusal the artefact lists, keyed by the case name it is written
     * under, in the natural order of its code.
     *
     * @param  array<mixed, mixed>  $artefact
     * @return array<string, array{code: string, status: int, description: string}>
     *
     * @throws UnexpectedValueException naming what is wrong, where the list is malformed
     */
    public function listed(array $artefact): array
    {
        if (! array_key_exists(self::KEY, $artefact)) {
            return [];
        }

        $listed = $artefact[self::KEY];

        if (! is_array($listed)) {
            throw new UnexpectedValueException('The vendored contract lists its refusals as something other than an object keyed by code.');
        }

        ksort($listed, SORT_NATURAL);

        $named = [];

        foreach ($listed as $code => $refusal) {
            if (! is_string($code) || trim($code) === '') {
                throw new UnexpectedValueException(sprintf('The vendored contract lists a refusal under `%s`, which is not a code.', $code));
            }

            $entry = $this->entry($code, $refusal);
            $case = $this->caseName($code, $entry['name']);

            if (array_key_exists($case, $named)) {
                throw new UnexpectedValueException(sprintf(
                    'The refusals `%s` and `%s` would both be named %s.',
                    $named[$case]['code'],
                    $code,
                    $case,
                ));
            }

            $named[$case] = ['code' => $code, 'status' => $entry['status'], 'description' => $entry['description']];
        }

        return $named;
    }

    /**
     * One refusal's entry, checked field by field.
     *
     * @return array{name: string, status: int, description: string}
     *
     * @throws UnexpectedValueException
     */
    private function entry(string $code, mixed $refusal): array
    {
        if (! is_array($refusal)) {
            throw new UnexpectedValueException(sprintf('The refusal `%s` is not an object.', $code));
        }

        $name = $refusal['name'] ?? null;

        if (! is_string($name) || preg_match(self::SCREAMING_SNAKE, $name) !== 1) {
            throw new UnexpectedValueException(sprintf('The refusal `%s` carries no name in SCREAMING_SNAKE.', $code));
        }

        $status = $refusal['status'] ?? null;

        if (! is_int($status) || $status < self::LOWEST || $status > self::HIGHEST) {
            throw new UnexpectedValueException(sprintf('The refusal `%s` carries no whole-number status a refusal is answered with.', $code));
        }

        $description = $refusal['description'] ?? null;

        if (! is_string($description) || trim($description) === '') {
            throw new UnexpectedValueException(sprintf('The refusal `%s` carries no description.', $code));
        }

        return ['name' => $name, 'status' => $status, 'description' => $description];
    }

    /**
     * The enum case a refusal is written under: its name in PascalCase.
     *
     * @throws UnexpectedValueException
     */
    private function caseName(string $code, string $name): string
    {
        $case = '';

        foreach (explode('_', $name) as $word) {
            $case .= ucfirst(strtolower($word));
        }

        if (strtolower($case) === 'class') {
            throw new UnexpectedValueException(sprintf('The refusal `%s` is named %s, which PHP does not take as a case name.', $code, $case));
        }

        return $case;
    }
}
