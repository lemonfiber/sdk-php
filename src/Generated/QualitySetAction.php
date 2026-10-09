<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: f41d39fb329202bbb14dcfedf2e1fdd83d855751, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\ActionRequest;

/**
 * The `quality-set` action, as the contract lists it.
 *
 * `confirm` carries the operator's yes to what it would cost.
 */
final class QualitySetAction extends ActionRequest
{
    /**
     * The action's name, as the command line spells it.
     */
    public const string ACTION = 'quality-set';

    /**
     * @param  string|null  $preset  The quality preset, or the music format, as it is written.
     * @param  string|null  $mediaType  The media type a quality choice applies to.
     * @param  bool  $confirm  Carries the operator's yes. Whether a cost the action would incur was agreed to in advance.
     */
    public function __construct(
        public readonly ?string $preset = null,
        public readonly ?string $mediaType = null,
        public readonly bool $confirm = false,
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
        return ['confirm'];
    }

    /**
     * @return array<string, mixed>
     */
    protected function arguments(): array
    {
        return [
            'preset' => $this->preset,
            'media_type' => $this->mediaType,
            'confirm' => $this->confirm,
        ];
    }
}
