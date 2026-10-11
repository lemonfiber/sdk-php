<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: 2a1472c24a6e4bb8d5ff70a792d61cc4b61e26b8, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\ActionRequest;

/**
 * The `search` action, as the contract lists it.
 */
final class SearchAction extends ActionRequest
{
    /**
     * The action's name, as the command line spells it.
     */
    public const string ACTION = 'search';

    /**
     * @param  bool  $disruptive  Whether the checks that disturb the running system are included.
     * @param  string|null  $term  What to follow, named as a person would say it.
     * @param  int<0, max>|null  $season  The season a followed show is narrowed to, or every season where absent.
     */
    public function __construct(
        public readonly bool $disruptive = false,
        public readonly ?string $term = null,
        public readonly ?int $season = null,
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
            'disruptive' => $this->disruptive,
            'term' => $this->term,
            'season' => $this->season,
        ];
    }
}
