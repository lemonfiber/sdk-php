<?php

declare(strict_types=1);

use Lemonfiber\Sdk\Client;
use Lemonfiber\Sdk\Http\BaseUrl;
use Lemonfiber\Sdk\Http\CertificatePin;
use Lemonfiber\Sdk\Http\LemonfiberConnector;
use Lemonfiber\Sdk\Http\RunToken;
use Lemonfiber\Sdk\Time\Duration;

/**
 * A pause short enough that a test sees a read asked again without waiting for it.
 */
function aMoment(): Duration
{
    return Duration::ofMilliseconds(1);
}

/**
 * A client on this port, as `Client::onPort()` builds one, pausing only a moment before a read is asked again.
 */
function aClientOnPort(int $port, string $token): Client
{
    return pausingAMoment(new LemonfiberConnector(BaseUrl::onPort($port), RunToken::fromString($token)));
}

/**
 * A client at this address, as `Client::at()` builds one, pausing only a moment before a read is asked again.
 */
function aClientAt(string $address, string $token): Client
{
    return pausingAMoment(new LemonfiberConnector(BaseUrl::fromString($address), RunToken::fromString($token)));
}

/**
 * A client held to this pin, as `Client::pinnedAt()` builds one, pausing only a moment before a read is asked again.
 */
function aPinnedClient(string $address, string $token, string $certificateDigest): Client
{
    return pausingAMoment(new LemonfiberConnector(
        BaseUrl::pinned($address, CertificatePin::fromSha256($certificateDigest)),
        RunToken::fromString($token),
    ));
}

/** A client over this connector, told to pause only a moment. */
function pausingAMoment(LemonfiberConnector $connector): Client
{
    $connector->pausingFor(aMoment());

    return new Client($connector);
}
