<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: 31d55133baccd7e5f7330103c0b9485ee3a174b8, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\ActionRequest;

/**
 * The `companion-pair` action, as the contract lists it.
 */
final class CompanionPairAction extends ActionRequest
{
    /**
     * The action's name, as the command line spells it.
     */
    public const string ACTION = 'companion-pair';

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
        return [];
    }
}
