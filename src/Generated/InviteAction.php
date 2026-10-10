<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: dc6670838b58c27ef1b1c64966f911492ff92c66, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\ActionRequest;

/**
 * The `invite` action, as the contract lists it.
 *
 * `confirm` carries the operator's yes to what it would cost.
 */
final class InviteAction extends ActionRequest
{
    /**
     * The action's name, as the command line spells it.
     */
    public const string ACTION = 'invite';

    /**
     * @param  string|null  $name  Who an invitation is for, as they will sign in.
     * @param  list<string>  $libraries  The libraries an invitation lets them open, by the names the media server gives those libraries. Empty is every one.
     * @param  int<0, max>|null  $ageLimit  The age above which an invitation has the media server hold things back.
     * @param  string|null  $unrated  What is to happen to content the media server has no rating for.  A word rather than a switch, because the choice has a cost either way and a switch has a default nobody can see: `block` holds unrated content back, and `allow` lets it through. Carried as it was written and read next door, so a word this build does not know is refused by name rather than falling to whichever answer the shape happened to default to.
     * @param  bool  $confirm  Carries the operator's yes. Whether a cost the action would incur was agreed to in advance.
     */
    public function __construct(
        public readonly ?string $name = null,
        public readonly array $libraries = [],
        public readonly ?int $ageLimit = null,
        public readonly ?string $unrated = null,
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
            'name' => $this->name,
            'libraries' => $this->libraries,
            'age_limit' => $this->ageLimit,
            'unrated' => $this->unrated,
            'confirm' => $this->confirm,
        ];
    }
}
