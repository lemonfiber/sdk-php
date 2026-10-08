<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: c8b5ffb1983acf50ff270be8a1a930a1c98cfb2c, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\ActionRequest;

/**
 * The `reset` action, as the contract lists it.
 *
 * `confirm` carries the operator's yes to what it would cost.
 */
final class ResetAction extends ActionRequest
{
    /**
     * The action's name, as the command line spells it.
     */
    public const string ACTION = 'reset';

    /**
     * @param  bool  $confirm  Carries the operator's yes. Whether a cost the action would incur was agreed to in advance.
     */
    public function __construct(
        public readonly bool $confirm = false,
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
        return ['confirm'];
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
            'confirm' => $this->confirm,
        ];
    }
}
