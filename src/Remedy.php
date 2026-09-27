<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk;

/**
 * One thing lemonfiber says to do about a refusal, in its own words.
 */
final readonly class Remedy
{
    public function __construct(private string $action, private ?string $detail) {}

    /**
     * What to do.
     */
    public function action(): string
    {
        return $this->action;
    }

    /**
     * The detail lemonfiber gave beside it, or none where it gave none.
     */
    public function detail(): ?string
    {
        return $this->detail;
    }
}
