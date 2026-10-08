<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Tests\Support;

use Lemonfiber\Sdk\ActionRequest;
use Override;

/**
 * An action as a generated class writes one: a name, its arguments, the consent among them, and a rehearsal.
 */
final class AnAction extends ActionRequest
{
    /**
     * @param  list<string>  $forms
     */
    public function __construct(private readonly array $forms = []) {}

    #[Override]
    public function action(): string
    {
        return 'stop-seeding';
    }

    #[Override]
    public function consent(): array
    {
        return ['confirm'];
    }

    public function rehearsed(): static
    {
        return $this->asRehearsal();
    }

    #[Override]
    protected function arguments(): array
    {
        return ['forms' => $this->forms, 'confirm' => false];
    }
}
