<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Scripts;

use function array_change_key_case;
use function array_diff;
use function array_is_list;
use function array_key_exists;
use function array_values;
use function explode;
use function implode;
use function in_array;
use function is_array;
use function is_bool;
use function is_string;
use function lcfirst;
use function preg_match;
use function sprintf;
use function strtolower;
use function ucfirst;

use UnexpectedValueException;

/**
 * The actions a contract artefact lists, read and checked.
 *
 * Keyed by action name. Each entry names the action as the command line does,
 * lists the arguments it takes with the JSON Schema type of each, names the
 * arguments that carry the operator's yes to what it would cost, and says
 * whether it takes `dry_run`. An artefact listing none lists an empty set.
 *
 * @phpstan-import-type Argument from ActionArgument
 *
 * @phpstan-type Action array{action: string, rehearsal: bool, consent: list<string>, arguments: list<Argument>}
 */
final readonly class Actions
{
    /** An action's name, as the command line spells one. */
    public const string NAME = '/^[a-z][a-z0-9]*(?:-[a-z0-9]+)*$/';

    /** What every generated action class is named with, after the action's own name. */
    public const string SUFFIX = 'Action';

    private const string KEY = 'actions';

    /** An argument's name, as the wire spells one. */
    private const string ARGUMENT = '/^[a-z][a-z0-9]*(?:_[a-z0-9]+)*$/';

    /**
     * An action's name in PascalCase, as a class or an enum case is written.
     */
    public static function pascal(string $action): string
    {
        $pascal = '';

        foreach (explode('-', $action) as $word) {
            $pascal .= ucfirst($word);
        }

        return $pascal;
    }

    /**
     * Every action the artefact lists, keyed by the class it is written as, in the contract's order.
     *
     * @param  array<mixed, mixed>  $artefact
     * @return array<string, Action>
     *
     * @throws UnexpectedValueException naming what is wrong, where the list is malformed
     */
    public function listed(array $artefact): array
    {
        if (! array_key_exists(self::KEY, $artefact)) {
            return [];
        }

        $listed = $artefact[self::KEY];

        if (! is_array($listed) || ($listed !== [] && array_is_list($listed))) {
            throw new UnexpectedValueException('The vendored contract lists its actions as something other than a map from each name to the action.');
        }

        $classes = [];

        foreach ($listed as $name => $entry) {
            $read = $this->action((string) $name, $entry);
            $class = self::pascal($read['action']) . self::SUFFIX;

            // Told apart without case as well, since a file system may not tell them apart.
            if (array_key_exists(strtolower($class), array_change_key_case($classes))) {
                throw new UnexpectedValueException(sprintf('The action `%s` would be written as %s, as another action already is.', $name, $class));
            }

            $classes[$class] = $read;
        }

        return $classes;
    }

    /**
     * One action, checked field by field.
     *
     * @return Action
     *
     * @throws UnexpectedValueException
     */
    private function action(string $name, mixed $entry): array
    {
        if (preg_match(self::NAME, $name) !== 1) {
            throw new UnexpectedValueException(sprintf('The vendored contract lists an action named `%s`, which is no action name.', $name));
        }

        if (! is_array($entry) || ($entry['action'] ?? null) !== $name) {
            throw new UnexpectedValueException(sprintf('The action `%s` is not described as itself.', $name));
        }

        $rehearsal = $entry['rehearsal'] ?? null;

        if (! is_bool($rehearsal)) {
            throw new UnexpectedValueException(sprintf('The action `%s` does not say whether it can be rehearsed.', $name));
        }

        $arguments = $this->arguments($name, $entry['arguments'] ?? null);

        return [
            'action' => $name,
            'rehearsal' => $rehearsal,
            'consent' => $this->consent($name, $entry['consent'] ?? null, $arguments),
            'arguments' => $arguments,
        ];
    }

    /**
     * @return list<Argument>
     *
     * @throws UnexpectedValueException
     */
    private function arguments(string $action, mixed $listed): array
    {
        if (! is_array($listed) || ! array_is_list($listed)) {
            throw new UnexpectedValueException(sprintf('The action `%s` lists its arguments as something other than a list.', $action));
        }

        $read = [];

        foreach ($listed as $argument) {
            $name = is_array($argument) ? ($argument['name'] ?? null) : null;

            if (! is_string($name) || preg_match(self::ARGUMENT, $name) !== 1) {
                throw new UnexpectedValueException(sprintf('The action `%s` lists an argument with no name an argument can have.', $action));
            }

            if (array_key_exists($name, $read)) {
                throw new UnexpectedValueException(sprintf('The action `%s` lists its argument `%s` twice.', $action, $name));
            }

            $type = $argument['type'] ?? null;

            if (! is_array($type)) {
                throw new UnexpectedValueException(sprintf('The action `%s` gives its argument `%s` no type.', $action, $name));
            }

            $read[$name] = new ActionArgument($action, $name, $this->camel($name))->read($type);
        }

        return array_values($read);
    }

    /**
     * @param  list<Argument>  $arguments
     * @return list<string>
     *
     * @throws UnexpectedValueException
     */
    private function consent(string $action, mixed $listed, array $arguments): array
    {
        if (! is_array($listed) || ! array_is_list($listed)) {
            throw new UnexpectedValueException(sprintf('The action `%s` names the arguments carrying consent as something other than a list.', $action));
        }

        $taken = [];

        foreach ($arguments as $argument) {
            $taken[] = $argument['wire'];
        }

        $named = [];

        foreach ($listed as $name) {
            if (! is_string($name) || in_array($name, $named, true)) {
                throw new UnexpectedValueException(sprintf('The action `%s` names an argument carrying consent that is not a name, or names one twice.', $action));
            }

            $named[] = $name;
        }

        $unknown = array_diff($named, $taken);

        if ($unknown !== []) {
            throw new UnexpectedValueException(sprintf(
                'The action `%s` says consent travels in `%s`, which it does not take.',
                $action,
                implode('`, `', $unknown),
            ));
        }

        return $named;
    }

    /**
     * A wire name as a PHP parameter is written: `age_limit` as `ageLimit`.
     */
    private function camel(string $name): string
    {
        $camel = '';

        foreach (explode('_', $name) as $word) {
            $camel .= ucfirst($word);
        }

        return lcfirst($camel);
    }
}
