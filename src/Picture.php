<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk;

use function explode;
use function in_array;

use Lemonfiber\Sdk\Exception\UnreadableResponse;

use function strlen;
use function strtolower;
use function trim;

/**
 * One of a title's pictures as lemonfiber handed it over: a raster image of at most
 * {@see self::MOST_BYTES} bytes, and the type it was labelled with.
 */
final readonly class Picture
{
    /**
     * The types a picture arrives as: raster images, which carry nothing a browser runs.
     */
    public const array MEDIA_TYPES = ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/avif'];

    /**
     * The most bytes a picture is.
     */
    public const int MOST_BYTES = 2 * 1024 * 1024;

    /**
     * @param value-of<self::MEDIA_TYPES> $mediaType
     */
    private function __construct(
        private string $bytes,
        private string $mediaType,
    ) {}

    /**
     * The picture that arrived, labelled with the type the answer's header gave.
     *
     * @throws UnreadableResponse where the label is not one of {@see self::MEDIA_TYPES}, or the
     *                            bytes are more than {@see self::MOST_BYTES}
     */
    public static function handedOver(string $bytes, ?string $contentType): self
    {
        if ($contentType === null) {
            throw UnreadableResponse::pictureUnlabelled();
        }

        $mediaType = strtolower(trim(explode(';', $contentType)[0]));
        if (! in_array($mediaType, self::MEDIA_TYPES, true)) {
            throw UnreadableResponse::notAPicture($contentType);
        }

        if (strlen($bytes) > self::MOST_BYTES) {
            throw UnreadableResponse::pictureTooLarge();
        }

        return new self($bytes, $mediaType);
    }

    /**
     * The picture, byte for byte.
     */
    public function bytes(): string
    {
        return $this->bytes;
    }

    /**
     * The type it was labelled with, without its parameters and in lower case.
     *
     * @return value-of<self::MEDIA_TYPES>
     */
    public function mediaType(): string
    {
        return $this->mediaType;
    }
}
