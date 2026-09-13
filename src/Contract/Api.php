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
     * The `api_version` integer this client speaks.
     *
     * Taken from the contract the types were generated from, so the wire
     * version is stated once.
     */
    public const int VERSION = Contract::API_VERSION;

    /**
     * The header every request carries the per-run token in.
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
     * The endpoint answering with a diagnosis.
     *
     * Named here rather than by whoever asks, which is the same argument
     * {@see self::EVENTS_ENDPOINT} makes: the contract carries envelope kinds
     * and no endpoints, so a path is knowledge this client holds on its
     * callers' behalf, and a caller spelling one is a caller that breaks
     * silently the day lemonfiber moves it.
     *
     * It runs `doctor` and answers with the `doctor` envelope, so
     * {@see \Lemonfiber\Sdk\Generated\DoctorEnvelope} is what reads it. A
     * read, so it neither accepts a warning nor opts into the checks that
     * disturb a running system — both of those change something and are
     * actions.
     */
    public const string CHECKS_ENDPOINT = '/api/checks';

    /**
     * The media type live updates arrive as.
     */
    public const string EVENT_STREAM_MEDIA_TYPE = 'text/event-stream';

    /**
     * The media type every other answer arrives as.
     */
    public const string JSON_MEDIA_TYPE = 'application/json';
}
