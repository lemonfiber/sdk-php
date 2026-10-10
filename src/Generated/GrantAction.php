<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: 0e9fbff3d7b423c54967af31b0b77d083e37b0a0, api_version 1.
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
     * @param  string|null  $name  Who an invitation is for, as they will sign in.
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
