<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: c4e98c06e6a6aab2127ae392414bfb9f088b038d, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\ActionRequest;

/**
 * The `push-woken` action, as the contract lists it.
 */
final class PushWokenAction extends ActionRequest
{
    /**
     * The action's name, as the command line spells it.
     */
    public const string ACTION = 'push-woken';

    /**
     * @param  string|null  $name  Who an action addressed to somebody in the household is for, as they sign in.
     * @param  string|null  $id  The thing an action is about, by its id: the title or episode a player's progress is on, by the identifier the shelf lists it under, the title a member asks for, by the identifier their search listed it under, or the wake a phone confirms.
     */
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?string $id = null,
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
            'name' => $this->name,
            'id' => $this->id,
        ];
    }
}
