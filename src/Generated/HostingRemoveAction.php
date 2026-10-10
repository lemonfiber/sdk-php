<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: 0e9fbff3d7b423c54967af31b0b77d083e37b0a0, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\ActionRequest;

/**
 * The `hosting-remove` action, as the contract lists it.
 */
final class HostingRemoveAction extends ActionRequest
{
    /**
     * The action's name, as the command line spells it.
     */
    public const string ACTION = 'hosting-remove';

    /**
     * @param  string|null  $kept  Which command this machine is being asked to keep running, or stop keeping.  A word rather than a switch, for the reason `policy` is one: there is more than one of them, and a word this build does not know is refused by name rather than silently taken for whichever the shape happened to default to.
     */
    public function __construct(
        public readonly ?string $kept = null,
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
            'kept' => $this->kept,
        ];
    }
}
