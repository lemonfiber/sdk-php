<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: ebe81f0581db0d09c3025f7f745d5fa71883085a, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\ActionRequest;

/**
 * The `down` action, as the contract lists it.
 */
final class DownAction extends ActionRequest
{
    /**
     * The action's name, as the command line spells it.
     */
    public const string ACTION = 'down';

    /**
     * @param  list<string>  $forms  The forms to act on.
     * @param  list<string>  $services  The services to act on, leaving the rest of the form alone.
     * @param  bool  $wait  Whether anything still downloading is let finish before the stop.
     */
    public function __construct(
        public readonly array $forms = [],
        public readonly array $services = [],
        public readonly bool $wait = false,
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
            'forms' => $this->forms,
            'services' => $this->services,
            'wait' => $this->wait,
        ];
    }
}
