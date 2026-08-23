<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Events;

use Iterator;

/**
 * Somewhere bytes of a live stream come from.
 */
interface EventSource
{
    /**
     * Opens the stream, resuming after `$lastEventId` when one is given.
     *
     * An empty chunk stands for a wait that ended with nothing arriving.
     *
     * @return Iterator<int, string>
     */
    public function open(?string $lastEventId): Iterator;
}
