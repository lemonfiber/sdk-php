<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk;

/**
 * A code to sign a member in by, as the door issued it.
 */
final readonly class CodeIssued
{
    /**
     * @param string $code                 the code the member approves
     * @param string $approveAt            where the member approves it
     * @param int    $lapsesAtEpochSeconds when it lapses
     * @param string $waiting              the handle that finishes it, kept in memory only
     */
    public function __construct(
        public string $code,
        public string $approveAt,
        public int $lapsesAtEpochSeconds,
        public string $waiting,
    ) {}
}
