<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Exception;

use RuntimeException;

use function sprintf;

/**
 * lemonfiber turned the request down.
 */
final class RequestFailed extends RuntimeException implements Problem
{
    private function __construct(
        private readonly string $endpoint,
        private readonly int $status,
        string $message,
    ) {
        parent::__construct($message);
    }

    public static function from(string $endpoint, int $status): self
    {
        return new self($endpoint, $status, sprintf(
            'lemonfiber turned down the request for %s and answered %d. Nothing was taken from that answer.',
            $endpoint,
            $status,
        ));
    }

    /**
     * The endpoint that was asked.
     */
    public function endpoint(): string
    {
        return $this->endpoint;
    }

    /**
     * The status lemonfiber answered with.
     */
    public function status(): int
    {
        return $this->status;
    }
}
