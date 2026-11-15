<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: fc07885a2d4c8498362bdcaf6539f91033928867, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\ActionRequest;

/**
 * The `tune` action, as the contract lists it.
 */
final class TuneAction extends ActionRequest
{
    /**
     * The action's name, as the command line spells it.
     */
    public const string ACTION = 'tune';

    /**
     * @param  string|null  $id  The thing an action is about, by its id: the title or episode a player's progress is on, by the identifier the shelf lists it under, the title a member asks for, by the identifier their search listed it under, or the wake a phone confirms.
     * @param  string|null  $stream  The live stream a renewal or a stop names, as a channel opened for the member answered it.
     * @param  bool|null  $stop  Whether to close the live stream named rather than renew its passes.
     */
    public function __construct(
        public readonly ?string $id = null,
        public readonly ?string $stream = null,
        public readonly ?bool $stop = null,
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
            'id' => $this->id,
            'stream' => $this->stream,
            'stop' => $this->stop,
        ];
    }
}
