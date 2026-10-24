<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: ceba0455d870b24e85d274b8fe8b85d130b9fa75, api_version 1.
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
