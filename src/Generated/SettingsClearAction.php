<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: 9e38845c8b03b2ec3957cc27dcf1a23b8441f765, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\ActionRequest;

/**
 * The `settings-clear` action, as the contract lists it.
 *
 * `offer` carries the operator's yes to what it would cost.
 */
final class SettingsClearAction extends ActionRequest
{
    /**
     * The action's name, as the command line spells it.
     */
    public const string ACTION = 'settings-clear';

    /**
     * @param  string|null  $setting  The setting to change or put back to its default, by the name the settings read lists it under.
     * @param  string|null  $offer  Carries the operator's yes. What was read before answering — the offer a repair's yes was read in, the listing a restore's was, what a replacement would stop — as it named itself.
     */
    public function __construct(
        public readonly ?string $setting = null,
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
            'setting' => $this->setting,
            'offer' => $this->offer,
        ];
    }
}
