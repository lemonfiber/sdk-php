<?php

declare(strict_types=1);

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\ConnectTimeoutException;
use GuzzleHttp\Exception\NetworkTimeoutException;
use GuzzleHttp\Exception\ResponseTimeoutException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Lemonfiber\Sdk\Http\WhatTheTransportReported;
use Lemonfiber\Sdk\WhyNothingAnswered;

/** A request the transport was sending when it raised. */
function aRequestItWasSending(): Request
{
    return new Request('GET', 'https://192.168.1.42:8443/api/status');
}

it('reads which way nothing answered off the transport\'s words', function (string $said, WhyNothingAnswered $why): void {
    expect(WhatTheTransportReported::in(new ConnectException($said, aRequestItWasSending())))->toBe($why);
})->with([
    'a name PHP could not look up' => ['php_network_getaddresses: getaddrinfo for loft.local failed: No address associated with hostname', WhyNothingAnswered::NameNotFound],
    'a name looked up nowhere' => ['php_network_getaddresses: getaddrinfo for loft.invalid failed: Name or service not known', WhyNothingAnswered::NameNotFound],
    'a name curl could not look up' => ['cURL error 6: Could not resolve host: loft.local', WhyNothingAnswered::NameNotFound],
    'a name the stream could not look up for one family' => ["Could not resolve IPv4 address for host 'loft.local'", WhyNothingAnswered::NameNotFound],
    'a connection turned away' => ['fopen(https://127.0.0.1:59102/api/status): Failed to open stream: Connection refused', WhyNothingAnswered::Refused],
    'a connection curl could not make, and said no more' => ['cURL error 7: Failed to connect to 127.0.0.1 port 59098 after 0 ms: Could not connect to server', WhyNothingAnswered::Refused],
    'a connection curl could not make, with no way to it' => ['cURL error 7: Failed to connect to 192.168.1.42 port 8443 after 3 ms: No route to host', WhyNothingAnswered::NoRoute],
    'a connection curl could not make, waited out' => ['cURL error 7: Failed to connect to 192.168.1.42 port 8443 after 5001 ms: Timed out', WhyNothingAnswered::TimedOut],
    'a machine that turned it away on another platform' => ['No connection could be made because the target machine actively refused it', WhyNothingAnswered::Refused],
    'nothing on the network answering for the address' => ['stream_socket_client(): Unable to connect to ssl://192.168.1.42:8443 (No route to host)', WhyNothingAnswered::NoRoute],
    'a host the network says is unreachable' => ['Unable to connect to ssl://192.168.1.42:8443 (Host is unreachable)', WhyNothingAnswered::NoRoute],
    'a network with no way out' => ['Unable to connect to ssl://192.168.1.42:8443 (Network is unreachable)', WhyNothingAnswered::NoRoute],
    'a host that is down' => ['Unable to connect to ssl://192.168.1.42:8443 (Host is down)', WhyNothingAnswered::NoRoute],
    'a wait written out in words' => ['Unable to connect to ssl://192.168.1.42:8443 (Connection timed out)', WhyNothingAnswered::TimedOut],
    'a call with no time left for the attempt' => ['The call used all the time it was given before this attempt could be made.', WhyNothingAnswered::TimedOut],
    'words nobody matches' => ['The connection was reset', WhyNothingAnswered::Other],
]);

it('reads a wait that ran out off the type either transport raises it as', function (Throwable $raised): void {
    expect(WhatTheTransportReported::in($raised))->toBe(WhyNothingAnswered::TimedOut);
})->with([
    'while connecting' => [new ConnectTimeoutException('took too long', aRequestItWasSending())],
    'on the way' => [new NetworkTimeoutException('took too long', aRequestItWasSending())],
    'while the answer came' => [new ResponseTimeoutException('took too long', aRequestItWasSending(), new Response())],
]);

it('reads what was wrapped where the transport\'s exception was wrapped', function (): void {
    $wrapped = new RuntimeException('wrapped', 0, new ConnectException('cURL error 6: Could not resolve host', aRequestItWasSending()));

    expect(WhatTheTransportReported::in($wrapped))->toBe(WhyNothingAnswered::NameNotFound);
});
