<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: 10531156453569a9e96110e9dddb5d472faf8aeb, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\ActionRequest;

/**
 * The `space` action, as the contract lists it.
 *
 * `offer` carries the operator's yes to what it would cost.
 */
final class SpaceAction extends ActionRequest
{
    /**
     * The action's name, as the command line spells it.
     */
    public const string ACTION = 'space';

    /**
     * @param  list<string>  $only  The categories of what the offer holds as costing nothing to take, orphaned or extracted; all it holds where none is named.
     * @param  string|null  $offer  Carries the operator's yes. What was read before answering — the offer a repair's yes was read in, the listing a restore's was, what a replacement would stop — as it named itself.
     */
    public function __construct(
        public readonly array $only = [],
        public readonly ?string $offer = null,
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
            'only' => $this->only,
            'offer' => $this->offer,
        ];
    }
}
