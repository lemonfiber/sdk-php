<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Exception;

use InvalidArgumentException;

use function sprintf;

/**
 * A value supplied to this client cannot be used.
 */
final class ConfigurationProblem extends InvalidArgumentException implements Problem
{
    public static function unreadableAddress(string $address): self
    {
        return new self(sprintf(
            'The address "%s" could not be read. Give it as http://127.0.0.1 followed by the port lemonfiber printed when it started.',
            $address,
        ));
    }

    public static function addressIsNotOnThisMachine(string $host): self
    {
        return new self(sprintf(
            'lemonfiber answers only on the machine it runs on. The address "%s" points somewhere else, so nothing was sent to it.',
            $host,
        ));
    }

    public static function unsupportedScheme(string $scheme): self
    {
        return new self(sprintf(
            'The address has to start with http or https. This one starts with "%s".',
            $scheme,
        ));
    }

    public static function addressCarriesExtras(): self
    {
        return new self(
            'The address may carry a host, a port and a path, and nothing else. Sign-in details, a query and a fragment are not accepted here.',
        );
    }

    public static function portOutOfRange(int $port): self
    {
        return new self(sprintf(
            'A port is a number from 1 to 65535. %d falls outside that range.',
            $port,
        ));
    }

    public static function tokenIsEmpty(): self
    {
        return new self(
            'No run token was given. lemonfiber prints a fresh one each time it starts, and every request has to carry it.',
        );
    }

    public static function tokenHasHiddenCharacters(): self
    {
        return new self(
            'The run token holds characters that cannot travel in a request. Copy it again from what lemonfiber printed.',
        );
    }

    public static function lengthOfTimeNotPositive(int $milliseconds): self
    {
        return new self(sprintf(
            'A length of time has to be more than zero milliseconds. %d was given.',
            $milliseconds,
        ));
    }

    public static function reconnectLimitBelowZero(int $limit): self
    {
        return new self(sprintf(
            'The number of reconnections to allow cannot be less than zero. %d was given.',
            $limit,
        ));
    }
}
