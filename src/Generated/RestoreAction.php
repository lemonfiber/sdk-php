<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: cf9a18239dd3e3d710ae8d9d9d8916ab255b799f, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\ActionRequest;

/**
 * The `restore` action, as the contract lists it.
 *
 * `offer`, `confirm` carry the operator's yes to what it would cost.
 */
final class RestoreAction extends ActionRequest
{
    /**
     * The action's name, as the command line spells it.
     */
    public const string ACTION = 'restore';

    /**
     * @param  string|null  $archive  The backup to restore from, by the name it was written under.
     * @param  bool  $repoint  Whether re-pointing to this machine's data root was accepted.
     * @param  string|null  $offer  Carries the operator's yes. What was read before answering — the offer a repair's yes was read in, the listing a restore's was, what a replacement would stop — as it named itself.
     * @param  bool  $confirm  Carries the operator's yes. Whether a cost the action would incur was agreed to in advance.
     */
    public function __construct(
        public readonly ?string $archive = null,
        public readonly bool $repoint = false,
        public readonly ?string $offer = null,
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
        return ['offer', 'confirm'];
    }

    /**
     * @return array<string, mixed>
     */
    protected function arguments(): array
    {
        return [
            'archive' => $this->archive,
            'repoint' => $this->repoint,
            'offer' => $this->offer,
            'confirm' => $this->confirm,
        ];
    }
}
