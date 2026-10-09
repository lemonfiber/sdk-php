<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Exception;

use function implode;

use Lemonfiber\Sdk\Picture;
use RuntimeException;

use function sprintf;

/**
 * An answer arrived in a shape this client cannot read.
 */
final class UnreadableResponse extends RuntimeException implements AnswerUnusable
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

    public static function endingUnreadable(string $written): self
    {
        return new self(sprintf(
            'The session came back with an ending this client cannot read as a moment: %s.',
            $written,
        ));
    }

    public static function hostUnreadable(): self
    {
        return new self(
            'The answer names the machine it is about with something other than a name.',
        );
    }

    public static function dataMissing(): self
    {
        return new self(
            'The answer carries no data.',
        );
    }

    public static function notAPicture(?string $contentType): self
    {
        return new self(sprintf(
            'The answer is not a picture this client shows: it was labelled %s, and a picture is one of %s.',
            $contentType === null ? 'with no type' : '"' . $contentType . '"',
            implode(', ', Picture::MEDIA_TYPES),
        ));
    }

    public static function pictureTooLarge(): self
    {
        return new self(sprintf(
            'The answer is larger than the %d bytes a picture is at most, so none of it was kept.',
            Picture::MOST_BYTES,
        ));
    }
}
