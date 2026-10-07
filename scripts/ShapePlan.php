<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Scripts;

use function array_key_exists;
use function array_keys;
use function array_pop;
use function array_unique;
use function array_values;
use function count;
use function in_array;
use function is_array;
use function is_string;
use function json_encode;
use function ksort;
use function min;
use function sort;
use function sprintf;
use function str_starts_with;
use function strlen;
use function substr;

use UnexpectedValueException;

/**
 * Where each shape the contract defines is named, once.
 *
 * Every definition a kind's payload reaches becomes a PHPStan type alias. One
 * only a single kind reaches is named on that kind's envelope class; one more
 * than one kind reaches is named on {@see SHARED}, and imported where it is
 * used. A reference inside a cycle of definitions stays `mixed`, since an alias
 * may not be defined through itself.
 */
final class ShapePlan
{
    /** The class every shape more than one kind reaches is named on. */
    public const string SHARED = 'Shapes';

    /** The alias each envelope class names its own payload with. */
    public const string PAYLOAD = 'Data';

    private const string PREFIX = '#/$defs/';

    /** @var array<string, array<mixed, mixed>> each definition's schema, by name */
    private array $schemas = [];

    /** @var array<string, list<string>> each definition's kinds, by name */
    private array $users = [];

    /** @var array<string, list<string>> the definitions each definition refers to */
    private array $refers = [];

    /** @var array<string, int> the cycle each definition is part of */
    private array $cycle = [];

    /** @var array<string, int> the order the walk over references reached each definition in */
    private array $index = [];

    /** @var array<string, int> the earliest definition each one's references lead back to */
    private array $low = [];

    /** @var list<string> the definitions the walk has reached and not yet placed in a cycle */
    private array $stack = [];

    private int $counter = 0;

    private int $components = 0;

    /**
     * @param  array<mixed, mixed>  $kinds  each kind's schema, by kind
     * @param  list<string>  $taken  the names generated classes already hold
     *
     * @throws UnexpectedValueException naming a definition this plan cannot name
     */
    public function __construct(array $kinds, array $taken)
    {
        foreach ($kinds as $kind => $schema) {
            if (is_string($kind) && is_array($schema)) {
                $this->collect($kind, $schema, $taken);
            }
        }

        ksort($this->schemas);
        $this->cycles();
    }

    /**
     * The class a definition is named on: its one kind's, or {@see SHARED}.
     */
    public function owner(string $name): ?string
    {
        $users = $this->users[$name] ?? [];

        return count($users) === 1 ? $users[0] : ($users === [] ? null : self::SHARED);
    }

    /**
     * Whether a reference from `$from` to `$to` closes a cycle, and so stays `mixed`.
     */
    public function closesACycle(?string $from, string $to): bool
    {
        return $from !== null && ($this->cycle[$from] ?? -1) === ($this->cycle[$to] ?? -2);
    }

    /**
     * Whether a name is a definition this plan names.
     */
    public function names(string $name): bool
    {
        return array_key_exists($name, $this->users);
    }

    /**
     * Each definition named on a class, by name, with its schema.
     *
     * @return array<string, array<mixed, mixed>>
     */
    public function namedOn(string $owner): array
    {
        $named = [];

        foreach ($this->schemas as $name => $schema) {
            if ($this->owner($name) === $owner) {
                $named[$name] = $schema;
            }
        }

        return $named;
    }

    /**
     * The definitions named elsewhere that a class's schemas refer to, each with the class naming it.
     *
     * @param  list<array<mixed, mixed>>  $schemas
     * @return array<string, string>
     */
    public function importsFor(string $owner, array $schemas): array
    {
        $imports = [];

        foreach ($schemas as $schema) {
            foreach ($this->referencesIn($schema) as $name) {
                $from = $this->owner($name);

                if ($from !== null && $from !== $owner) {
                    $imports[$name] = $from;
                }
            }
        }

        ksort($imports);

        return $imports;
    }

    /**
     * Every definition a schema refers to directly.
     *
     * @param  array<mixed, mixed>  $schema
     * @return list<string>
     */
    public function referencesIn(array $schema): array
    {
        $found = array_values(array_unique($this->gathered($schema)));
        sort($found);

        return $found;
    }

    /**
     * @param  array<mixed, mixed>  $schema
     * @param  list<string>  $taken
     *
     * @throws UnexpectedValueException
     */
    private function collect(string $kind, array $schema, array $taken): void
    {
        $defs = $schema['$defs'] ?? [];
        $defs = is_array($defs) ? $defs : [];

        foreach ($defs as $name => $definition) {
            if (! is_string($name) || ! is_array($definition)) {
                continue;
            }

            if (in_array($name, $taken, true) || $name === self::PAYLOAD || $name === self::SHARED) {
                throw new UnexpectedValueException(sprintf('The definition `%s`, carried by `%s`, takes a name a generated class or alias already holds.', $name, $kind));
            }

            if (array_key_exists($name, $this->schemas) && json_encode($this->schemas[$name]) !== json_encode($definition)) {
                throw new UnexpectedValueException(sprintf('The definition `%s` is carried by more than one kind as two different shapes.', $name));
            }

            $this->schemas[$name] = $definition;
            $this->refers[$name] = $this->referencesIn($definition);
        }

        $properties = $schema['properties'] ?? null;
        $payload = is_array($properties) && is_array($properties['data'] ?? null) ? $properties['data'] : [];
        $pending = $this->referencesIn($payload);
        $reached = [];

        while ($pending !== []) {
            $name = array_pop($pending);

            if (in_array($name, $reached, true) || ! array_key_exists($name, $defs)) {
                continue;
            }

            $reached[] = $name;
            $definition = $defs[$name];
            $pending = [...$pending, ...(is_array($definition) ? $this->referencesIn($definition) : [])];
        }

        foreach ($reached as $name) {
            $this->users[$name][] = $kind;
            sort($this->users[$name]);
        }
    }

    /**
     * Every reference a schema holds, nested ones included, as often as it holds each.
     *
     * @param  array<mixed, mixed>  $node
     * @return list<string>
     */
    private function gathered(array $node): array
    {
        $reference = $node['$ref'] ?? null;
        $found = is_string($reference) && str_starts_with($reference, self::PREFIX) ? [substr($reference, strlen(self::PREFIX))] : [];

        foreach ($node as $key => $child) {
            if ($key !== '$defs' && is_array($child)) {
                $found = [...$found, ...$this->gathered($child)];
            }
        }

        return $found;
    }

    /**
     * Numbers each definition by the cycle of references it is part of, one of its own where it is in none.
     */
    private function cycles(): void
    {
        foreach (array_keys($this->schemas) as $name) {
            if (! array_key_exists($name, $this->index)) {
                $this->visit($name);
            }
        }
    }

    /**
     * One step of Tarjan's walk over the definitions' references.
     */
    private function visit(string $name): void
    {
        $this->index[$name] = $this->counter;
        $this->low[$name] = $this->counter;
        $this->counter++;
        $this->stack[] = $name;

        foreach ($this->refers[$name] ?? [] as $next) {
            if (! array_key_exists($next, $this->schemas)) {
                continue;
            }

            if (! array_key_exists($next, $this->index)) {
                $this->visit($next);
                $this->low[$name] = min($this->low[$name], $this->low[$next]);
            } elseif (in_array($next, $this->stack, true)) {
                $this->low[$name] = min($this->low[$name], $this->index[$next]);
            }
        }

        if ($this->low[$name] !== $this->index[$name]) {
            return;
        }

        do {
            $member = (string) array_pop($this->stack);
            $this->cycle[$member] = $this->components;
        } while ($member !== $name);

        $this->components++;
    }
}
