<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk;

/**
 * A code the member has not approved yet.
 */
final readonly class CodeStillWaiting
{
    /**
     * @param string $waiting              the handle that finishes it
     * @param int    $lapsesAtEpochSeconds when it lapses
     */
    public function __construct(
        public string $waiting,
        public int $lapsesAtEpochSeconds,
    ) {}
}
