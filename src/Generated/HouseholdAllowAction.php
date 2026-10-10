<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: cf9a18239dd3e3d710ae8d9d9d8916ab255b799f, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\ActionRequest;

/**
 * The `household-allow` action, as the contract lists it.
 */
final class HouseholdAllowAction extends ActionRequest
{
    /**
     * The action's name, as the command line spells it.
     */
    public const string ACTION = 'household-allow';

    /**
     * @param  string|null  $name  Who an invitation is for, as they will sign in.
     * @param  string|null  $policy  What is to happen to what the household asks for, as it is written.  A word rather than a switch, for the reason `unrated` is one: there are three answers rather than two, and a word this build does not know is refused by name instead of falling to whichever arrangement the shape happened to default to.
     * @param  int<0, max>|null  $requests  How many requests a period allows.
     * @param  int<0, max>|null  $days  How long that period is, in days.
     */
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?string $policy = null,
        public readonly ?int $requests = null,
        public readonly ?int $days = null,
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
            'name' => $this->name,
            'policy' => $this->policy,
            'requests' => $this->requests,
            'days' => $this->days,
        ];
    }
}
