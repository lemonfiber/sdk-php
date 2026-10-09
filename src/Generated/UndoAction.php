<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: dc6670838b58c27ef1b1c64966f911492ff92c66, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\ActionRequest;

/**
 * The `undo` action, as the contract lists it.
 */
final class UndoAction extends ActionRequest
{
    /**
     * The action's name, as the command line spells it.
     */
    public const string ACTION = 'undo';

    /**
     * @param  string|null  $at  The stamp of the run to put back, as the history writes it.
     */
    public function __construct(
        public readonly ?string $at = null,
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
            'at' => $this->at,
        ];
    }
}
