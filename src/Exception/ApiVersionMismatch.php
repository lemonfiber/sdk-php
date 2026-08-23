<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Exception;

use RuntimeException;

use function sprintf;

/**
 * The answer speaks a different version of the API than this client (ARCH-R55).
 */
final class ApiVersionMismatch extends RuntimeException implements Problem
{
    private function __construct(
        private readonly int $spoken,
        private readonly int $answered,
        string $message,
    ) {
        parent::__construct($message);
    }

    public static function between(int $spoken, int $answered): self
    {
        return new self($spoken, $answered, sprintf(
            'This client speaks version %d of the lemonfiber API and the answer came back as version %d. Nothing was taken from that answer. Update whichever of the two is older, then try again.',
            $spoken,
            $answered,
        ));
    }

    /**
     * The version this client speaks.
     */
    public function spoken(): int
    {
        return $this->spoken;
    }

    /**
     * The version the answer arrived as.
     */
    public function answered(): int
    {
        return $this->answered;
    }
}
