<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: 640f134910d8eecc146aa1c2531c4c7d2b0f177f, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\ActionRequest;

/**
 * The `update` action, as the contract lists it.
 *
 * `offer`, `confirm` carry the operator's yes to what it would cost.
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
     * @param  string|null  $offer  Carries the operator's yes. What was read before answering — the offer a repair's yes was read in, the listing a restore's was, what a replacement would stop — as it named itself.
     * @param  bool  $confirm  Carries the operator's yes. Whether a cost the action would incur was agreed to in advance.
     */
    public function __construct(
        public readonly bool $wait = false,
        public readonly ?string $service = null,
        public readonly ?string $offer = null,
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
        return ['offer', 'confirm'];
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
            'offer' => $this->offer,
            'confirm' => $this->confirm,
        ];
    }
}
