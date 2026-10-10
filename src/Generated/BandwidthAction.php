<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: cf9a18239dd3e3d710ae8d9d9d8916ab255b799f, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\ActionRequest;

/**
 * The `bandwidth` action, as the contract lists it.
 */
final class BandwidthAction extends ActionRequest
{
    /**
     * The action's name, as the command line spells it.
     */
    public const string ACTION = 'bandwidth';

    /**
     * @param  string|null  $down  How much of the line downloads may take, as a share or a rate.  Carried as it was written and read in the core, like `unrated` above and for the same reason: a surface that decided what `50%` meant would be a second answer to the question, and the two would part company on the first change to either — which a household would meet as an evening that went wrong on one surface and not on another.
     * @param  string|null  $up  How much of it uploads may take, set apart from the download.
     * @param  string|null  $active  The hours the household is awake, as `HH:MM-HH:MM` on the wall clock.
     * @param  string|null  $line  What the line carries, as `<down>/<up>`, where the operator knows.
     * @param  string|null  $cap  A monthly allowance for what the stack itself moves.
     * @param  string|null  $exceeded  What is to happen when that cap is reached, decided in advance.
     * @param  int<0, max>|null  $unrestrictedFor  How many minutes to lift the limits for, and no longer.
     */
    public function __construct(
        public readonly ?string $down = null,
        public readonly ?string $up = null,
        public readonly ?string $active = null,
        public readonly ?string $line = null,
        public readonly ?string $cap = null,
        public readonly ?string $exceeded = null,
        public readonly ?int $unrestrictedFor = null,
    ) {}

    public function action(): string
    {
        return self::ACTION;
    }

    /**
     * @return list<string>
     */
    public function consent(): array
    {
        return [];
    }

    /**
     * The same request, asking what it would do rather than doing it.
     */
    public function rehearsed(): static
    {
        return $this->asRehearsal();
    }

    /**
     * @return array<string, mixed>
     */
    protected function arguments(): array
    {
        return [
            'down' => $this->down,
            'up' => $this->up,
            'active' => $this->active,
            'line' => $this->line,
            'cap' => $this->cap,
            'exceeded' => $this->exceeded,
            'unrestricted_for' => $this->unrestrictedFor,
        ];
    }
}
