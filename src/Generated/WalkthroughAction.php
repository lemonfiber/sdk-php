<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: 6a2d9bbd6d8c0b7a09258f6352d427c89b8bc2b6, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\ActionRequest;

/**
 * The `walkthrough` action, as the contract lists it.
 */
final class WalkthroughAction extends ActionRequest
{
    /**
     * The action's name, as the command line spells it.
     */
    public const string ACTION = 'walkthrough';

    /**
     * @param  string|null  $item  The one thing to add end to end, as it would be said.
     */
    public function __construct(
        public readonly ?string $item = null,
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
            'item' => $this->item,
        ];
    }
}
