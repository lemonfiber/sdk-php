<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk;

use function preg_match;

/**
 * One support bundle as lemonfiber handed it over: the file itself, and what
 * the transport said about it.
 *
 * The bytes are the archive exactly as it arrived; nothing here opens, reads or
 * checks it. The type and the length are what the answer's headers carried,
 * each absent where the answer carried none, and neither is derived from the
 * bytes: a length the transport did not state is not one this client states.
 */
final readonly class BundleFile
{
    /**
     * A length as a header states one: digits and nothing else.
     */
    private const string WHOLE_NUMBER = '/\A\d+\z/';

    private function __construct(
        private string $name,
        private string $bytes,
        private ?string $contentType,
        private ?int $length,
    ) {}

    /**
     * The file that arrived for a name, with the headers that came with it.
     *
     * A length header holding anything but a whole number is read as no length.
     */
    public static function handedOver(string $name, string $bytes, ?string $contentType, ?string $length): self
    {
        return new self(
            $name,
            $bytes,
            $contentType,
            $length !== null && preg_match(self::WHOLE_NUMBER, $length) === 1 ? (int) $length : null,
        );
    }

    /**
     * The name the bundle was asked for by.
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * The file, byte for byte.
     */
    public function bytes(): string
    {
        return $this->bytes;
    }

    /**
     * The type the answer was labelled with, or none where it carried no label.
     */
    public function contentType(): ?string
    {
        return $this->contentType;
    }

    /**
     * The length the answer stated, or none where it stated none.
     */
    public function length(): ?int
    {
        return $this->length;
    }
}
