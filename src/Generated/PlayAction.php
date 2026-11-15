<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: 6a2d9bbd6d8c0b7a09258f6352d427c89b8bc2b6, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\ActionRequest;

/**
 * The `play` action, as the contract lists it.
 */
final class PlayAction extends ActionRequest
{
    /**
     * The action's name, as the command line spells it.
     */
    public const string ACTION = 'play';

    /**
     * @param  string|null  $device  The id a player keeps for the device it plays on, which a grant opens a session for.
     * @param  string|null  $id  The thing an action is about, by its id: the title or episode a player's progress is on, by the identifier the shelf lists it under, the title a member asks for, by the identifier their search listed it under, or the wake a phone confirms.
     * @param  string|null  $pass  The pass a browser holds, handed back for a new one around the same session.
     */
    public function __construct(
        public readonly ?string $device = null,
        public readonly ?string $id = null,
        public readonly ?string $pass = null,
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
            'device' => $this->device,
            'id' => $this->id,
            'pass' => $this->pass,
        ];
    }
}
