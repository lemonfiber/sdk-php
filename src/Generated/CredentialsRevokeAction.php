<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: b81064077ef45a4fdc104931ecace46d1e85f55f, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\ActionRequest;

/**
 * The `credentials-revoke` action, as the contract lists it.
 *
 * `offer` carries the operator's yes to what it would cost.
 */
final class CredentialsRevokeAction extends ActionRequest
{
    /**
     * The action's name, as the command line spells it.
     */
    public const string ACTION = 'credentials-revoke';

    /**
     * @param  string|null  $offer  Carries the operator's yes. What was read before answering — the offer a repair's yes was read in, the listing a restore's was, what a replacement would stop — as it named itself.
     * @param  string|null  $plugin  The plugin an update or a removal acts on, by the id the record of what is installed lists it under.
     * @param  string|null  $credential  The credential a grant to a third-party plugin is of, by the setting the credentials read lists it under.
     */
    public function __construct(
        public readonly ?string $offer = null,
        public readonly ?string $plugin = null,
        public readonly ?string $credential = null,
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
            'offer' => $this->offer,
            'plugin' => $this->plugin,
            'credential' => $this->credential,
        ];
    }
}
