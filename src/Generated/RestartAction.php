<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: ebe81f0581db0d09c3025f7f745d5fa71883085a, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\ActionRequest;

/**
 * The `restart` action, as the contract lists it.
 *
 * `offer` carries the operator's yes to what it would cost.
 */
final class RestartAction extends ActionRequest
{
    /**
     * The action's name, as the command line spells it.
     */
    public const string ACTION = 'restart';

    /**
     * @param  list<string>  $forms  The forms to act on.
     * @param  list<string>  $services  The services to act on, leaving the rest of the form alone.
     * @param  string|null  $offer  Carries the operator's yes. What was read before answering — the offer a repair's yes was read in, the listing a restore's was, what a replacement would stop — as it named itself.
     */
    public function __construct(
        public readonly array $forms = [],
        public readonly array $services = [],
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
            'forms' => $this->forms,
            'services' => $this->services,
            'offer' => $this->offer,
        ];
    }
}
