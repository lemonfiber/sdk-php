<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: e0d5ee48e087ddd488e3bbade0614c2bdb8df72f, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\ActionRequest;

/**
 * The `watch` action, as the contract lists it.
 */
final class WatchAction extends ActionRequest
{
    /**
     * The action's name, as the command line spells it.
     */
    public const string ACTION = 'watch';

    /**
     * @param  list<string>  $forms  The forms to act on.
     */
    public function __construct(
        public readonly array $forms = [],
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
        ];
    }
}
