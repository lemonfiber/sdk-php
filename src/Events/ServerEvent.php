<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Events;

/**
 * One update as it arrived on the stream.
 */
final readonly class ServerEvent
{
    public function __construct(
        public string $kind,
        public string $data,
        public ?string $id = null,
    ) {}
}
