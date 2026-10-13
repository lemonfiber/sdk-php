<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk;

use function base64_encode;
use function rtrim;
use function strtr;

/**
 * Bytes written as the stack reads them in a request body: base64url, unpadded.
 *
 * @internal
 */
final class Base64Url
{
    /**
     * `$bytes` in base64url, unpadded.
     */
    public static function encoded(string $bytes): string
    {
        return rtrim(strtr(base64_encode($bytes), '+/', '-_'), '=');
    }
}
