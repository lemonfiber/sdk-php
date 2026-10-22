<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: 11738d67882155f12773108d2fe7c3bd80fd7c2b, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\ActionRequest;

/**
 * The `reissue` action, as the contract lists it.
 */
final class ReissueAction extends ActionRequest
{
    /**
     * The action's name, as the command line spells it.
     */
    public const string ACTION = 'reissue';

    /**
     * @param  string|null  $name  Who an action addressed to somebody in the household is for, as they sign in.
     */
    public function __construct(
        public readonly ?string $name = null,
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
        ];
    }
}
