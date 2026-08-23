<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Contract;

use Lemonfiber\Sdk\Generated\Contract;

/**
 * The wire contract this client speaks.
 */
final class Api
{
    /**
     * The `api_version` integer this client speaks (ARCH-R9, ARCH-R54).
     *
     * Taken from the contract the types were generated from, so the wire
     * version is stated once (ARCH-R58).
     */
    public const int VERSION = Contract::API_VERSION;

    /**
     * The header every request carries the per-run token in (ARCH-R52).
     */
    public const string TOKEN_HEADER = 'X-Lemonfiber-Token';

    /**
     * The header a resumed stream names its last seen event in.
     */
    public const string RESUME_HEADER = 'Last-Event-ID';

    /**
     * The endpoint serving live updates.
     */
    public const string EVENTS_ENDPOINT = '/api/events';

    /**
     * The media type live updates arrive as.
     */
    public const string EVENT_STREAM_MEDIA_TYPE = 'text/event-stream';

    /**
     * The media type every other answer arrives as.
     */
    public const string JSON_MEDIA_TYPE = 'application/json';
}
