<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Tests\Support;

use const E_USER_WARNING;

use function stream_wrapper_register;
use function stream_wrapper_unregister;
use function trigger_error;

/**
 * A stream whose every read breaks, the way an encrypted stream does when the network under it goes away.
 *
 * The platform reports such a read twice: with a warning, and with a read that
 * hands back nothing at all. This does the same.
 */
final class AStreamThatBreaks
{
    /** The scheme it is opened under. */
    public const string SCHEME = 'a-stream-that-breaks';

    /** @var resource|null set by PHP for every stream wrapper */
    public $context;

    public static function register(): void
    {
        stream_wrapper_register(self::SCHEME, self::class);
    }

    public static function unregister(): void
    {
        stream_wrapper_unregister(self::SCHEME);
    }

    public function stream_open(): bool
    {
        return true;
    }

    public function stream_read(): false
    {
        trigger_error('SSL: Software caused connection abort', E_USER_WARNING);
        return false;
    }

    public function stream_eof(): bool
    {
        return false;
    }

    public function stream_set_option(): bool
    {
        return true;
    }
}
