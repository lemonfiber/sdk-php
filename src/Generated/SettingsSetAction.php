<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: 472e2fca5aaf2ed4a5cfaf553df6a8714e2164cc, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\ActionRequest;

/**
 * The `settings-set` action, as the contract lists it.
 *
 * `offer` carries the operator's yes to what it would cost.
 */
final class SettingsSetAction extends ActionRequest
{
    /**
     * The action's name, as the command line spells it.
     */
    public const string ACTION = 'settings-set';

    /**
     * @param  string|null  $value  What to change the key or the setting to, written as exactly itself.
     * @param  string|null  $setting  The setting to change or put back to its default, by the name the settings read lists it under.
     * @param  string|null  $offer  Carries the operator's yes. What was read before answering — the offer a repair's yes was read in, the listing a restore's was, what a replacement would stop — as it named itself.
     */
    public function __construct(
        public readonly ?string $value = null,
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
            'value' => $this->value,
            'setting' => $this->setting,
            'offer' => $this->offer,
        ];
    }
}
