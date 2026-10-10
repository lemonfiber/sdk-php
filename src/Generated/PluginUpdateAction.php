<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: cf9a18239dd3e3d710ae8d9d9d8916ab255b799f, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\ActionRequest;

/**
 * The `plugin-update` action, as the contract lists it.
 *
 * `offer`, `approved` carry the operator's yes to what it would cost.
 */
final class PluginUpdateAction extends ActionRequest
{
    /**
     * The action's name, as the command line spells it.
     */
    public const string ACTION = 'plugin-update';

    /**
     * @param  string|null  $offer  Carries the operator's yes. What was read before answering — the offer a repair's yes was read in, the listing a restore's was, what a replacement would stop — as it named itself.
     * @param  string|null  $plugin  The plugin an update or a removal acts on, by the id the record of what is installed lists it under.
     * @param  string|null  $source  Where a plugin comes from: its name in the catalogue, its directory on this machine or the `plugin.toml` inside it, or a git repository, at a branch, tag or commit named after its last `@`.
     * @param  list<string>  $approved  Carries the operator's yes. Every value a recipe would carry elsewhere that is approved, as `value@destination`, and every privileged shape, as `shape@service`, exactly as the reading lists them.
     * @param  list<string>  $inputs  Every value the operator supplies for an input a recipe asks for, as `name=value`. What follows the `=` may be a secret, and is never repeated back.
     */
    public function __construct(
        public readonly ?string $offer = null,
        public readonly ?string $plugin = null,
        public readonly ?string $source = null,
        public readonly array $approved = [],
        public readonly array $inputs = [],
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
        return ['offer', 'approved'];
    }

    /**
     * @return array<string, mixed>
     */
    protected function arguments(): array
    {
        return [
            'offer' => $this->offer,
            'plugin' => $this->plugin,
            'source' => $this->source,
            'approved' => $this->approved,
            'inputs' => $this->inputs,
        ];
    }
}
