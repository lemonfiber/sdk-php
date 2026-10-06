<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Http;

use function array_any;

use GuzzleHttp\Exception\ConnectTimeoutException;
use GuzzleHttp\Exception\NetworkTimeoutException;
use GuzzleHttp\Exception\ResponseTimeoutException;
use Lemonfiber\Sdk\WhyNothingAnswered;

use function str_contains;
use function strtolower;

use Throwable;

/**
 * Which way a request met nothing, read off what the transport raised.
 *
 * A wait that ran out is raised as a type of its own by both transports, and
 * is read off the type. Every other way is read off the words, which curl and
 * PHP's own streams, the transport an address held to a pin is reached
 * through, both write in the system's terms. The words name the address they
 * failed on and are only matched here, never kept.
 */
final readonly class WhatTheTransportReported
{
    /**
     * What each way's words contain, as the transports write them, in the
     * order they are asked.
     *
     * A refusal comes last because curl's words for a connection it could not
     * make, `cURL error 7`, are its words for all three ways a connection fails:
     * where the system's own words beside them say no way or a wait, that is
     * the way; where they say nothing more, curl's number names a refusal first.
     */
    private const array WORDS = [
        'name_not_found' => ['getaddrinfo', 'could not resolve', 'no address associated', 'name or service not known', 'nodename nor servname'],
        'no_route' => ['no route to host', 'host is unreachable', 'network is unreachable', 'host is down'],
        'timed_out' => ['timed out', 'used all the time it was given'],
        'refused' => ['connection refused', 'actively refused', 'curl error 7:'],
    ];

    /** Which way the request met nothing, from what was raised or what it was raised over. */
    public static function in(Throwable $raised): WhyNothingAnswered
    {
        $transferred = $raised->getPrevious() ?? $raised;

        if (
            $transferred instanceof ConnectTimeoutException
            || $transferred instanceof NetworkTimeoutException
            || $transferred instanceof ResponseTimeoutException
        ) {
            return WhyNothingAnswered::TimedOut;
        }

        return self::byWords($transferred->getMessage());
    }

    /** The way the words name, the first whose words they contain. */
    private static function byWords(string $reported): WhyNothingAnswered
    {
        $said = strtolower($reported);

        foreach (self::WORDS as $way => $words) {
            if (array_any($words, static fn(string $word): bool => str_contains($said, $word))) {
                return WhyNothingAnswered::from($way);
            }
        }

        return WhyNothingAnswered::Other;
    }
}
