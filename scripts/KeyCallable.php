<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Scripts;

use function array_is_list;
use function array_key_exists;
use function is_array;
use function is_bool;
use function is_string;
use function preg_match;
use function sprintf;

use UnexpectedValueException;

/**
 * The actions an integration key may call, as a contract artefact lists them, read and checked.
 *
 * The list is in the contract's order. Each entry names the action as the
 * command line does, and says whether calling it disturbs the running system
 * and whether it takes `dry_run`. An artefact listing none lists an empty set.
 */
final readonly class KeyCallable
{
    private const string KEY = 'key_callable';

    /**
     * Every action the artefact lists, keyed by the case name it is written under, in its order.
     *
     * @param  array<mixed, mixed>  $artefact
     * @return array<string, array{action: string, disturbs: bool, rehearsal: bool}>
     *
     * @throws UnexpectedValueException naming what is wrong, where the list is malformed
     */
    public function listed(array $artefact): array
    {
        if (! array_key_exists(self::KEY, $artefact)) {
            return [];
        }

        $listed = $artefact[self::KEY];

        if (! is_array($listed) || ! array_is_list($listed)) {
            throw new UnexpectedValueException('The vendored contract lists the actions a key may call as something other than a list.');
        }

        $named = [];

        foreach ($listed as $at => $entry) {
            $read = $this->entry($at, $entry);
            $case = Actions::pascal($read['action']);

            if (array_key_exists($case, $named)) {
                throw new UnexpectedValueException(sprintf('The action `%s` is listed twice among those a key may call.', $read['action']));
            }

            $named[$case] = $read;
        }

        return $named;
    }

    /**
     * One entry, checked field by field.
     *
     * @return array{action: string, disturbs: bool, rehearsal: bool}
     *
     * @throws UnexpectedValueException
     */
    private function entry(int $at, mixed $entry): array
    {
        if (! is_array($entry)) {
            throw new UnexpectedValueException(sprintf('Entry %d of the actions a key may call is not an object.', $at));
        }

        $action = $entry['action'] ?? null;

        if (! is_string($action) || preg_match(Actions::NAME, $action) !== 1) {
            throw new UnexpectedValueException(sprintf('Entry %d of the actions a key may call names no action.', $at));
        }

        $disturbs = $entry['disturbs'] ?? null;
        $rehearsal = $entry['rehearsal'] ?? null;

        if (! is_bool($disturbs) || ! is_bool($rehearsal)) {
            throw new UnexpectedValueException(sprintf('The action `%s` does not say both whether it disturbs and whether it can be rehearsed.', $action));
        }

        return ['action' => $action, 'disturbs' => $disturbs, 'rehearsal' => $rehearsal];
    }
}
