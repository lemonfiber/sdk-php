<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Exception;

use RuntimeException;

use function sprintf;

/**
 * An answer arrived in a shape this client cannot read.
 */
final class UnreadableResponse extends RuntimeException implements Problem
{
    public static function notJson(string $detail): self
    {
        return new self(sprintf(
            'The answer was not readable as JSON: %s',
            $detail,
        ));
    }

    public static function notAnEnvelope(): self
    {
        return new self(
            'The answer was readable as JSON but was not the wrapper every lemonfiber answer arrives in.',
        );
    }

    public static function versionMissing(): self
    {
        return new self(
            'The answer carries no whole-number api_version, so there is no way to tell whether this client can read it.',
        );
    }

    public static function kindMissing(): self
    {
        return new self(
            'The answer carries no kind, so there is no way to tell what it holds.',
        );
    }

    public static function dataMissing(): self
    {
        return new self(
            'The answer carries no data.',
        );
    }
}
