<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: f41d39fb329202bbb14dcfedf2e1fdd83d855751, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\ActionRequest;

/**
 * The `accept` action, as the contract lists it.
 */
final class AcceptAction extends ActionRequest
{
    /**
     * The action's name, as the command line spells it.
     */
    public const string ACTION = 'accept';

    /**
     * @param  string|null  $check  The check a warning is being answered for.
     * @param  bool  $disruptive  Whether the checks that disturb the running system are included.
     */
    public function __construct(
        public readonly ?string $check = null,
        public readonly bool $disruptive = false,
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
            'check' => $this->check,
            'disruptive' => $this->disruptive,
        ];
    }
}
