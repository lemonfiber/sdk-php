<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: a1ca5f05d410c09480bf901a4c442e5b141aa1b7, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\ActionRequest;

/**
 * The `downloads-resume` action, as the contract lists it.
 */
final class DownloadsResumeAction extends ActionRequest
{
    /**
     * The action's name, as the command line spells it.
     */
    public const string ACTION = 'downloads-resume';

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
        return [];
    }
}
