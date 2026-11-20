<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk;

/**
 * A code the member has not approved yet.
 */
final readonly class CodeStillWaiting
{
    /**
     * @param WaitingCode $waiting        the handle that finishes it
     * @param int    $lapsesAtEpochSeconds when it lapses
     */
    public function __construct(
        public WaitingCode $waiting,
        public int $lapsesAtEpochSeconds,
    ) {}
}
