<?php

// Generated from contract/web-api.contract.json. Do not edit.
// Source: 23ed6123641f70109620e9e4b6ecb36365461d2f, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

/**
 * Every code the contract lists a refusal as carrying, and the status each is answered with.
 *
 * A code this list does not name is read by its status alone.
 */
enum RefusalCode: string
{
    /**
     * The case a code names, or none where there is no code or one this list does not name.
     */
    public static function of(?string $code): ?self
    {
        return $code === null ? null : self::tryFrom($code);
    }

    /**
     * The status a refusal carrying this code is answered with.
     */
    public function status(): int
    {
        return match ($this) {
        };
    }
}
