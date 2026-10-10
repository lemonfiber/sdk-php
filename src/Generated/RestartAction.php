<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: 0e9fbff3d7b423c54967af31b0b77d083e37b0a0, api_version 1.
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
