<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Http;

use function is_string;
use function preg_match;

use Saloon\Http\Response;

/**
 * One header of an answer.
 */
final readonly class Header
{
    /** The header an answer names its type in. */
    public const string CONTENT_TYPE = 'Content-Type';

    /** The header an answer states its length in. */
    public const string CONTENT_LENGTH = 'Content-Length';

    /** A length as a header states one: digits and nothing else. */
    public const string WHOLE_NUMBER = '/\A\d+\z/';

    /**
     * Whether the answer states a length of more than `$most` bytes. A length that is
     * not a whole number states none.
     */
    public static function declaresMoreThan(Response $response, int $most): bool
    {
        $length = self::in($response, self::CONTENT_LENGTH);

        return $length !== null && preg_match(self::WHOLE_NUMBER, $length) === 1 && (int) $length > $most;
    }

    /**
     * The one value an answer gave a header, or none where it gave none or
     * gave several.
     */
    public static function in(Response $response, string $name): ?string
    {
        /** @var array<mixed>|string|null $value */
        $value = $response->header($name);

        return is_string($value) ? $value : null;
    }
}
