<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: 34b804b67efd8a70494734f8d588b0c5f697e107, api_version 1.
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
