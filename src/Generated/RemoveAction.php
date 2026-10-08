<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: a1ca5f05d410c09480bf901a4c442e5b141aa1b7, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\ActionRequest;

/**
 * The `remove` action, as the contract lists it.
 *
 * `confirm` carries the operator's yes to what it would cost.
 */
final class RemoveAction extends ActionRequest
{
    /**
     * The action's name, as the command line spells it.
     */
    public const string ACTION = 'remove';

    /**
     * @param  string|null  $name  Who an invitation is for, as they will sign in.
     * @param  bool  $confirm  Carries the operator's yes. Whether a cost the action would incur was agreed to in advance.
     */
    public function __construct(
        public readonly ?string $name = null,
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
     * @return array<string, mixed>
     */
    protected function arguments(): array
    {
        return [
            'name' => $this->name,
            'confirm' => $this->confirm,
        ];
    }
}
