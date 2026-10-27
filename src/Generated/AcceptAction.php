<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: aaaf59f88495ca7c7f39c55a4d24cb9a3dcbb7d7, api_version 1.
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
     * @param  bool  $disruptive  Whether the checks that disturb the running system are included.
     * @param  string|null  $check  The check a warning is being answered for.
     */
    public function __construct(
        public readonly bool $disruptive,
        public readonly ?string $check = null,
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
