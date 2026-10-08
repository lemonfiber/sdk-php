<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: ad4e5f20666a8a10e0d0ed61f6a1a6a118614162, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\ActionRequest;

/**
 * The `update` action, as the contract lists it.
 *
 * `confirm` carries the operator's yes to what it would cost.
 */
final class UpdateAction extends ActionRequest
{
    /**
     * The action's name, as the command line spells it.
     */
    public const string ACTION = 'update';

    /**
     * @param  bool  $wait  Whether anything still downloading is let finish before the stop.
     * @param  string|null  $service  The one service to act on instead of the whole stack.
     * @param  bool  $confirm  Carries the operator's yes. Whether a cost the action would incur was agreed to in advance.
     */
    public function __construct(
        public readonly bool $wait = false,
        public readonly ?string $service = null,
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
            'wait' => $this->wait,
            'service' => $this->service,
            'confirm' => $this->confirm,
        ];
    }
}
