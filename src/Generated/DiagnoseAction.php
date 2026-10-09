<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: ebe81f0581db0d09c3025f7f745d5fa71883085a, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\ActionRequest;

/**
 * The `diagnose` action, as the contract lists it.
 */
final class DiagnoseAction extends ActionRequest
{
    /**
     * The action's name, as the command line spells it.
     */
    public const string ACTION = 'diagnose';

    /**
     * @param  string|null  $only  The group of checks, or the one check, a diagnosis is narrowed to.
     * @param  bool  $disruptive  Whether the checks that disturb the running system are included.
     */
    public function __construct(
        public readonly ?string $only = null,
        public readonly bool $disruptive = false,
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
            'only' => $this->only,
            'disruptive' => $this->disruptive,
        ];
    }
}
