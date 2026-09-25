<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Exception;

use function is_int;
use function is_string;
use function parse_url;
use function preg_match_all;

use RuntimeException;

use function sprintf;
use function strtr;

/**
 * No answer came back from lemonfiber at all.
 *
 * The other side of {@see RequestFailed}. A refusal is lemonfiber answering,
 * which says the connection works and the address is right; this is the
 * request meeting nothing — the connection could not be made, or it broke
 * before an answer arrived. The machine may be stopped or asleep, the address
 * may lead nowhere from here, or the network between the two may be down.
 *
 * **It does not say the request went unheard.** A connection that breaks after
 * the request was written may leave an action applied with its answer lost on
 * the way back. An action re-sent under the same attempt name is one act, and
 * that is what makes sending it again safe (see {@see \Lemonfiber\Sdk\Client::act()}).
 *
 * It carries what the connection reported, as {@see reason()}, with every
 * address in it cut back to where it points: sign-in details, a query and a
 * fragment are removed before the words are kept. The transport's own
 * exception is not kept as its cause, so no type of the transport reaches a
 * caller through it.
 */
final class Unreachable extends RuntimeException implements Problem
{
    /**
     * An address inside what the connection reported, up to the next space or
     * the punctuation that closes a phrase around it.
     */
    private const string AN_ADDRESS = '~([a-z][a-z0-9+.-]*)://[^\s;,()]+~i';

    /** What an address that cannot be taken apart is written as. */
    private const string AN_UNREADABLE_ADDRESS = '[an address]';

    private function __construct(
        private readonly string $endpoint,
        private readonly string $reason,
        string $message,
    ) {
        parent::__construct($message);
    }

    /**
     * The request for an endpoint, met by nothing, and what the connection said of it.
     */
    public static function whenAsking(string $endpoint, string $reported): self
    {
        return new self($endpoint, self::withheld($reported), sprintf(
            'No answer came back to the request for %s. lemonfiber may be stopped or asleep, or out of reach from here. Nothing was read from it.',
            $endpoint,
        ));
    }

    /**
     * The endpoint that was asked.
     */
    public function endpoint(): string
    {
        return $this->endpoint;
    }

    /**
     * What the connection reported, in its own words, with sign-in details, queries and fragments cut from every address in it.
     */
    public function reason(): string
    {
        return $this->reason;
    }

    /**
     * The words, with every address in them cut back to where it points.
     *
     * Every address is replaced in one pass, longest first, so an address that
     * begins another is not cut out of the longer one and leave its query behind.
     */
    private static function withheld(string $reported): string
    {
        preg_match_all(self::AN_ADDRESS, $reported, $found, PREG_SET_ORDER);

        $cut = [];

        foreach ($found as [$address, $scheme]) {
            $cut[$address] = self::whereItPoints($scheme, $address);
        }

        return strtr($reported, $cut);
    }

    /**
     * An address as its scheme, host, port and path, and nothing else.
     */
    private static function whereItPoints(string $scheme, string $address): string
    {
        $host = parse_url($address, PHP_URL_HOST);

        if (! is_string($host)) {
            return self::AN_UNREADABLE_ADDRESS;
        }

        $port = parse_url($address, PHP_URL_PORT);
        $path = parse_url($address, PHP_URL_PATH);

        return sprintf(
            '%s://%s%s%s',
            $scheme,
            $host,
            is_int($port) ? sprintf(':%d', $port) : '',
            is_string($path) ? $path : '',
        );
    }
}
