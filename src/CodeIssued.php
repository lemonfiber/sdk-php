<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk;

/**
 * A code to sign a member in by, as the door issued it.
 */
final readonly class CodeIssued
{
    /**
     * @param string $code                 the code the member approves, shown to them; it finishes nothing without the handle
     * @param string $approveAt            where the member approves it
     * @param int    $lapsesAtEpochSeconds when it lapses
     * @param WaitingCode $waiting        the handle that finishes it, kept in memory only
     */
    public function __construct(
        public string $code,
        public string $approveAt,
        public int $lapsesAtEpochSeconds,
        public WaitingCode $waiting,
    ) {}
}
