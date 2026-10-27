<?php

declare(strict_types=1);

use Lemonfiber\Sdk\Client;
use Lemonfiber\Sdk\Http\BaseUrl;
use Lemonfiber\Sdk\Http\CertificatePin;
use Lemonfiber\Sdk\Http\LemonfiberConnector;
use Lemonfiber\Sdk\Http\RunToken;
use Lemonfiber\Sdk\Tests\Support\GeneratedRefusals;
use Lemonfiber\Sdk\Time\Duration;
use Lemonfiber\Sdk\Time\SystemClock;

/**
 * A code the generated refusal list names in every process the suite runs in, a parallel worker's or a mutant's
 * included: loaded here, before any test file can load the committed list.
 */
const A_CODE_THE_LIST_NAMES = 'FIXTURE-1';

GeneratedRefusals::loadWith(A_CODE_THE_LIST_NAMES, [
    'name' => 'LISTED_FOR_THESE_TESTS',
    'status' => 403,
    'description' => 'Raised by nothing; listed so these tests have a code the list names.',
]);

/**
 * A wait long enough that no test's call runs out of it, so only a test about the wait meets one.
 */
function aWait(): Duration
{
    return Duration::ofSeconds(5);
}

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
    return new Client(new LemonfiberConnector(BaseUrl::onPort($port), aWait(), RunToken::fromString($token), new SystemClock(), aMoment()));
}

/**
 * A client at this address, as `Client::at()` builds one, pausing only a moment before a read is asked again.
 */
function aClientAt(string $address, string $token): Client
{
    return new Client(new LemonfiberConnector(BaseUrl::fromString($address), aWait(), RunToken::fromString($token), new SystemClock(), aMoment()));
}

/**
 * A client held to this pin, as `Client::pinnedAt()` builds one, pausing only a moment before a read is asked again.
 */
function aPinnedClient(string $address, string $token, string $certificateDigest): Client
{
    return new Client(new LemonfiberConnector(
        BaseUrl::pinned($address, CertificatePin::fromSha256($certificateDigest)),
        aWait(),
        RunToken::fromString($token),
        new SystemClock(),
        aMoment(),
    ));
}
