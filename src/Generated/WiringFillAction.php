<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: ebe81f0581db0d09c3025f7f745d5fa71883085a, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\ActionRequest;

/**
 * The `wiring-fill` action, as the contract lists it.
 *
 * `offer` carries the operator's yes to what it would cost.
 */
final class WiringFillAction extends ActionRequest
{
    /**
     * The action's name, as the command line spells it.
     */
    public const string ACTION = 'wiring-fill';

    /**
     * @param  string|null  $service  The one service to act on instead of the whole stack.
     * @param  string|null  $offer  Carries the operator's yes. What was read before answering — the offer a repair's yes was read in, the listing a restore's was, what a replacement would stop — as it named itself.
     * @param  string|null  $reason  Why a request is being turned down, or why a service was chosen to fill a capability.
     * @param  string|null  $capability  The capability whose filler is being chosen.
     */
    public function __construct(
        public readonly ?string $service = null,
        public readonly ?string $offer = null,
        public readonly ?string $reason = null,
        public readonly ?string $capability = null,
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
        return ['offer'];
    }

    /**
     * @return array<string, mixed>
     */
    protected function arguments(): array
    {
        return [
            'service' => $this->service,
            'offer' => $this->offer,
            'reason' => $this->reason,
            'capability' => $this->capability,
        ];
    }
}
