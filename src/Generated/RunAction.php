<?php

// Generated from the contract vendored under contract/. Do not edit.
// Contract::SOURCE names its revision and Contract::API_VERSION its wire version.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\ActionRequest;

/**
 * The `run` action, as the contract lists it.
 */
final class RunAction extends ActionRequest
{
    /**
     * The action's name, as the command line spells it.
     */
    public const string ACTION = 'run';

    /**
     * @param  string|null  $service  The one service to act on instead of the whole stack.
     * @param  bool  $now  Whether a run-and-exit service runs at once, whether or not one of its times fell due.
     */
    public function __construct(
        public readonly ?string $service = null,
        public readonly bool $now = false,
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
            'service' => $this->service,
            'now' => $this->now,
        ];
    }
}
