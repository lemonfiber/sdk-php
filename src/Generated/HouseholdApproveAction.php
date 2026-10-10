<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: 640f134910d8eecc146aa1c2531c4c7d2b0f177f, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\ActionRequest;

/**
 * The `household-approve` action, as the contract lists it.
 */
final class HouseholdApproveAction extends ActionRequest
{
    /**
     * The action's name, as the command line spells it.
     */
    public const string ACTION = 'household-approve';

    /**
     * @param  int|null  $request  The request being ruled on, by the number the request service files it under.
     */
    public function __construct(
        public readonly ?int $request = null,
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
        ];
    }
}
