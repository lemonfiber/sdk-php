<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Events;

/**
 * Whether a held value still stands for what is true now (ARCH-R51).
 */
enum Freshness
{
    case Current;

    case Stale;
}
