<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: c8b5ffb1983acf50ff270be8a1a930a1c98cfb2c, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\ActionRequest;

/**
 * The `repair` action, as the contract lists it.
 *
 * `offer`, `agreed`, `confirm` carry the operator's yes to what it would cost.
 */
final class RepairAction extends ActionRequest
{
    /**
     * The action's name, as the command line spells it.
     */
    public const string ACTION = 'repair';

    /**
     * @param  bool  $disruptive  Whether the checks that disturb the running system are included.
     * @param  string|null  $offer  Carries the operator's yes. What was read before answering — the offer a repair's yes was read in, the listing a restore's was, what a replacement would stop — as it named itself.
     * @param  list<string>  $agreed  Carries the operator's yes. The checks whose repairs were agreed to, as that offer names them.
     * @param  bool  $confirm  Carries the operator's yes. Whether a cost the action would incur was agreed to in advance.
     */
    public function __construct(
        public readonly bool $disruptive = false,
        public readonly ?string $offer = null,
        public readonly array $agreed = [],
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
        return ['offer', 'agreed', 'confirm'];
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
            'disruptive' => $this->disruptive,
            'offer' => $this->offer,
            'agreed' => $this->agreed,
            'confirm' => $this->confirm,
        ];
    }
}
