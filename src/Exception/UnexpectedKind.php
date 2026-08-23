<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Exception;

use RuntimeException;

use function sprintf;

/**
 * An envelope was read as a kind it does not carry.
 */
final class UnexpectedKind extends RuntimeException implements Problem
{
    private function __construct(
        private readonly string $wanted,
        private readonly string $carried,
        string $message,
    ) {
        parent::__construct($message);
    }

    public static function between(string $wanted, string $carried): self
    {
        return new self($wanted, $carried, sprintf(
            'This envelope carries the kind %s and was read as %s. What an envelope holds is shaped by its kind, so nothing was taken from it.',
            $carried,
            $wanted,
        ));
    }

    /**
     * The kind the envelope was read as.
     */
    public function wanted(): string
    {
        return $this->wanted;
    }

    /**
     * The kind the envelope carries.
     */
    public function carried(): string
    {
        return $this->carried;
    }
}
