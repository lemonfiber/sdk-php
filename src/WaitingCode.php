<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk;

use Closure;
use SensitiveParameter;

/**
 * The handle the door answers with a sign-in code, which finishes that sign-in once the code is approved.
 *
 * It opens a member's session, so it is held where no dump, export, trace or
 * serialisation reaches it: inside a closure, which none of them prints.
 */
final readonly class WaitingCode
{
    private const string HIDDEN = '(hidden)';

    /** @var Closure(): string */
    private Closure $handle;

    private function __construct(#[SensitiveParameter] string $handle)
    {
        $this->handle = static fn(): string => $handle;
    }

    /**
     * @return array<string, string>
     */
    public function __debugInfo(): array
    {
        return ['handle' => self::HIDDEN];
    }

    /** The handle as the door answered it. */
    public static function of(#[SensitiveParameter] string $handle): self
    {
        return new self($handle);
    }

    /** The handle itself, for the one request that finishes the sign-in. */
    public function reveal(): string
    {
        return ($this->handle)();
    }
}
