<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: f41d39fb329202bbb14dcfedf2e1fdd83d855751, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

/**
 * Every action an integration key may call, in the contract's order; any other is refused to a key.
 */
enum KeyCallableAction: string
{
    case Restart = 'restart';
    case Diagnose = 'diagnose';
    case Update = 'update';
    case DownloadsPause = 'downloads-pause';
    case DownloadsResume = 'downloads-resume';

    /**
     * The case an action names, or none where a key may not call it.
     */
    public static function of(string $action): ?self
    {
        return self::tryFrom($action);
    }

    /**
     * Whether calling it disturbs the running system.
     */
    public function disturbs(): bool
    {
        return match ($this) {
            self::Restart => true,
            self::Diagnose => true,
            self::Update => true,
            self::DownloadsPause => false,
            self::DownloadsResume => false,
        };
    }

    /**
     * Whether it takes `dry_run`, so it can be rehearsed before the real call is offered.
     */
    public function rehearsal(): bool
    {
        return match ($this) {
            self::Restart => true,
            self::Diagnose => false,
            self::Update => true,
            self::DownloadsPause => true,
            self::DownloadsResume => true,
        };
    }
}
