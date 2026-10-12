<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: 92aaa5d577c67686de2760f80c67325ec997a3f8, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\ActionRequest;

/**
 * The `grant` action, as the contract lists it.
 */
final class GrantAction extends ActionRequest
{
    /**
     * The action's name, as the command line spells it.
     */
    public const string ACTION = 'grant';

    /**
     * @param  string|null  $name  Who an action addressed to somebody in the household is for, as they sign in.
     * @param  string|null  $device  The id a player keeps for the device it plays on, which a grant opens a session for.
     */
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?string $device = null,
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
            'device' => $this->device,
        ];
    }
}
