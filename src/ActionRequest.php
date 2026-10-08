<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk;

use Lemonfiber\Sdk\Contract\Api;

/**
 * An action lemonfiber offers, with the arguments it is asked with.
 *
 * Every action is a class of its own under `Lemonfiber\Sdk\Generated`,
 * generated from the contract's list of actions: a constructor taking each
 * argument by name with the type and default the contract gives it, the
 * arguments carrying the operator's yes named by {@see self::consent()}, and
 * `rehearsed()` where the action takes `dry_run`. An argument the action does
 * not take, or a value of the wrong type, is a mistake PHP and static analysis
 * point at before anything is sent:
 *
 *     $client->act(new RestartAction(forms: ['tv']));
 *     $client->act(new RestartAction(forms: ['tv'])->rehearsed());
 *
 * A value: nothing here changes once it is built, and a rehearsal is a copy.
 */
abstract class ActionRequest
{
    /** The argument a rehearsal carries, asking what the action would do rather than doing it. */
    public const string DRY_RUN = 'dry_run';

    private bool $rehearsal = false;

    /**
     * The action's name, as the command line spells it.
     */
    abstract public function action(): string;

    /**
     * The arguments that carry the operator's yes to what the action would cost.
     *
     * @return list<string>
     */
    abstract public function consent(): array;

    /**
     * Where the action is asked for.
     */
    final public function endpoint(): string
    {
        return Api::action($this->action());
    }

    /**
     * What travels: every argument by the name the wire gives it, and `dry_run` on a rehearsal.
     *
     * @return array<string, mixed>
     */
    final public function payload(): array
    {
        return $this->rehearsal ? [...$this->arguments(), self::DRY_RUN => true] : $this->arguments();
    }

    /**
     * Every argument the action takes, by the name it travels under.
     *
     * @return array<string, mixed>
     */
    abstract protected function arguments(): array;

    /**
     * The same action, asking what it would do rather than doing it.
     *
     * Protected, and offered as `rehearsed()` only by an action the contract
     * says takes `dry_run`: one that does not would refuse the field.
     */
    final protected function asRehearsal(): static
    {
        return clone($this, ['rehearsal' => true]);
    }
}
