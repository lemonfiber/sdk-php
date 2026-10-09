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
    private const string WHOLE_NUMBER = '/\A\d+\z/';

    /**
     * A header's value as a whole number, or none where it is anything else.
     */
    public static function wholeNumber(?string $written): ?int
    {
        return $written !== null && preg_match(self::WHOLE_NUMBER, $written) === 1 ? (int) $written : null;
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
