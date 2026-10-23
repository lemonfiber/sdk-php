<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: 2fdb17698fa8603b4fd6649e0109bd01705f8b3b, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\ActionRequest;

/**
 * The `plugin-run` action, as the contract lists it.
 *
 * `offer`, `approved` carry the operator's yes to what it would cost.
 */
final class PluginRunAction extends ActionRequest
{
    /**
     * The action's name, as the command line spells it.
     */
    public const string ACTION = 'plugin-run';

    /**
     * @param  string|null  $offer  Carries the operator's yes. What was read before answering — the offer a repair's yes was read in, the listing a restore's was, what a replacement would stop — as it named itself.
     * @param  string|null  $plugin  The plugin an update or a removal acts on, by the id the record of what is installed lists it under.
     * @param  string|null  $recipe  The demand recipe a run asks for, by the id the plugin's reading lists it under.
     * @param  list<string>  $approved  Carries the operator's yes. Every value a recipe would carry elsewhere that is approved, as `value@destination`, and every privileged shape, as `shape@service`, exactly as the reading lists them.
     * @param  list<string>  $inputs  Every value the operator supplies for an input a recipe asks for, as `name=value`. What follows the `=` may be a secret, and is never repeated back.
     */
    public function __construct(
        public readonly ?string $offer = null,
        public readonly ?string $plugin = null,
        public readonly ?string $recipe = null,
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
            'offer' => $this->offer,
            'plugin' => $this->plugin,
            'recipe' => $this->recipe,
            'approved' => $this->approved,
            'inputs' => $this->inputs,
        ];
    }
}
