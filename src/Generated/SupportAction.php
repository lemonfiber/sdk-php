<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: dc6670838b58c27ef1b1c64966f911492ff92c66, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\ActionRequest;

/**
 * The `support` action, as the contract lists it.
 *
 * `confirm` carries the operator's yes to what it would cost.
 */
final class SupportAction extends ActionRequest
{
    /**
     * The action's name, as the command line spells it.
     */
    public const string ACTION = 'support';

    /**
     * @param  bool  $write  Whether to produce the bundle, rather than say what one would hold.
     * @param  int<0, max>|null  $logs  How many log lines to take from each service.
     * @param  bool  $filenames  Whether media filenames are shown rather than replaced.
     * @param  list<string>  $reveal  The settings to show as they are, named as the bundle names them.
     * @param  bool  $confirm  Carries the operator's yes. Whether a cost the action would incur was agreed to in advance.
     */
    public function __construct(
        public readonly bool $write = false,
        public readonly ?int $logs = null,
        public readonly bool $filenames = false,
        public readonly array $reveal = [],
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
            'write' => $this->write,
            'logs' => $this->logs,
            'filenames' => $this->filenames,
            'reveal' => $this->reveal,
            'confirm' => $this->confirm,
        ];
    }
}
