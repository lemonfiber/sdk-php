<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Http;

use function is_string;

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
