<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: c8b5ffb1983acf50ff270be8a1a930a1c98cfb2c, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\ActionRequest;

/**
 * The `stop-seeding` action, as the contract lists it.
 *
 * `offer` carries the operator's yes to what it would cost.
 */
final class StopSeedingAction extends ActionRequest
{
    /**
     * The action's name, as the command line spells it.
     */
    public const string ACTION = 'stop-seeding';

    /**
     * @param  string|null  $offer  Carries the operator's yes. What was read before answering — the offer a repair's yes was read in, the listing a restore's was, what a replacement would stop — as it named itself.
     * @param  string|null  $download  The completed download the client is to be asked to let go, by the name both sides use.
     */
    public function __construct(
        public readonly ?string $offer = null,
        public readonly ?string $download = null,
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
            'offer' => $this->offer,
            'download' => $this->download,
        ];
    }
}
