<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: c8b5ffb1983acf50ff270be8a1a930a1c98cfb2c, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\ActionRequest;

/**
 * The `household-decline` action, as the contract lists it.
 */
final class HouseholdDeclineAction extends ActionRequest
{
    /**
     * The action's name, as the command line spells it.
     */
    public const string ACTION = 'household-decline';

    /**
     * @param  int|null  $request  The request being ruled on, by the number the request service files it under.
     * @param  string|null  $reason  Why a request is being turned down, or why a service was chosen to fill a capability.
     */
    public function __construct(
        public readonly ?int $request = null,
        public readonly ?string $reason = null,
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
        return [];
    }

    /**
     * @return array<string, mixed>
     */
    protected function arguments(): array
    {
        return [
            'request' => $this->request,
            'reason' => $this->reason,
        ];
    }
}
