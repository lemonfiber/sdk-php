<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: 640f134910d8eecc146aa1c2531c4c7d2b0f177f, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\ActionRequest;

/**
 * The `config-set` action, as the contract lists it.
 *
 * `confirm` carries the operator's yes to what it would cost.
 */
final class ConfigSetAction extends ActionRequest
{
    /**
     * The action's name, as the command line spells it.
     */
    public const string ACTION = 'config-set';

    /**
     * @param  bool  $wait  Whether anything still downloading is let finish before the stop.
     * @param  string|null  $key  The setting to change.
     * @param  string|null  $value  What to change it to.
     * @param  bool  $confirm  Carries the operator's yes. Whether a cost the action would incur was agreed to in advance.
     */
    public function __construct(
        public readonly bool $wait = false,
        public readonly ?string $key = null,
        public readonly ?string $value = null,
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
            'wait' => $this->wait,
            'key' => $this->key,
            'value' => $this->value,
            'confirm' => $this->confirm,
        ];
    }
}
