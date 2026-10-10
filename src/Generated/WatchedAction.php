<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: 0e9fbff3d7b423c54967af31b0b77d083e37b0a0, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\ActionRequest;

/**
 * The `watched` action, as the contract lists it.
 */
final class WatchedAction extends ActionRequest
{
    /**
     * The action's name, as the command line spells it.
     */
    public const string ACTION = 'watched';

    /**
     * @param  string|null  $name  Who an invitation is for, as they will sign in.
     * @param  string|null  $id  The title or episode a player's progress is about, by the identifier the shelf lists it under.
     * @param  int<0, max>|null  $position  How far into it the member is, in whole seconds.
     * @param  bool|null  $ended  Whether the member finished it. Absent says nothing about finishing, which is a position like any other.
     */
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?string $id = null,
        public readonly ?int $position = null,
        public readonly ?bool $ended = null,
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
            'name' => $this->name,
            'id' => $this->id,
            'position' => $this->position,
            'ended' => $this->ended,
        ];
    }
}
