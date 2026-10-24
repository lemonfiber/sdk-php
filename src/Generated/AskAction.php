<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: ceba0455d870b24e85d274b8fe8b85d130b9fa75, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\ActionRequest;

/**
 * The `ask` action, as the contract lists it.
 */
final class AskAction extends ActionRequest
{
    /**
     * The action's name, as the command line spells it.
     */
    public const string ACTION = 'ask';

    /**
     * @param  string|null  $name  Who an action addressed to somebody in the household is for, as they sign in.
     * @param  string|null  $id  The title an action is about: the title or episode a player's progress is on, by the identifier the shelf lists it under, or the title a member asks for, by the identifier their search listed it under.
     * @param  string|null  $kind  Whether the title asked for is a series or a film, as a page of a member's search names it: `tv` or `movies`.
     * @param  list<int<0, max>>  $seasons  The seasons of a series asked for. None names every season not already here or on its way.
     */
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?string $id = null,
        public readonly ?string $kind = null,
        public readonly array $seasons = [],
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
            'kind' => $this->kind,
            'seasons' => $this->seasons,
        ];
    }
}
