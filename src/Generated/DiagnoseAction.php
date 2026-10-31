<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: 53eb6d48a3f5e00735d516e7240932a35bb7a5e0, api_version 1.
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
     * @param  bool  $disruptive  Whether the checks that disturb the running system are included.
     * @param  string|null  $only  The group of checks, or the one check, a diagnosis is narrowed to.
     */
    public function __construct(
        public readonly bool $disruptive,
        public readonly ?string $only = null,
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
