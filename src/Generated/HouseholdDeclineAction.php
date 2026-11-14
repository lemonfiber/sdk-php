<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: c4e98c06e6a6aab2127ae392414bfb9f088b038d, api_version 1.
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
     * @param  string|null  $request  The request being ruled on, by the reference the household list names it with: the request service's own number (`42`), or `music-<n>` or `books-<n>` for an ask the core keeps.
     * @param  string|null  $reason  Why a request is being turned down, or why a service was chosen to fill a capability.
     */
    public function __construct(
        public readonly ?string $request = null,
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
            'request' => $this->request,
            'reason' => $this->reason,
        ];
    }
}
