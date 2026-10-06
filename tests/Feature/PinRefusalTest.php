<?php

declare(strict_types=1);

use Lemonfiber\Sdk\Admission;
use Lemonfiber\Sdk\Contract\Api;
use Lemonfiber\Sdk\Exception\CertificateWasRefused;
use Lemonfiber\Sdk\Exception\Unreachable;
use Lemonfiber\Sdk\Http\BaseUrl;
use Lemonfiber\Sdk\Http\CertificatePin;
use Lemonfiber\Sdk\Http\PresentedCertificate;
use Lemonfiber\Sdk\Tests\Support\AnsweringListener;
use Lemonfiber\Sdk\Tests\Support\TlsListener;

const A_PIN_NOTHING_PRESENTS = 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa';

/** What is left of a call's wait when the certificate is asked for, in seconds. */
const A_PROBES_WAIT = 5.0;

/**
 * What one call raised against a peer.
 *
 * @param Closure(): mixed $ask
 */
function whatThePeerRaised(Closure $ask): Throwable
{
    try {
        $ask();
    } catch (Throwable $raised) {
        return $raised;
    }

    throw new RuntimeException('the call came back as though the peer had answered it');
}

it('refuses a peer presenting a certificate the pin does not name, wherever it was asked', function (string $endpoint, Closure $ask): void {
    $peer = TlsListener::start();
    $raised = whatThePeerRaised(static fn(): mixed => $ask($peer->address()));

    expect($raised)->toBeInstanceOf(CertificateWasRefused::class)
        ->and($raised->getPrevious())->toBeNull();

    if ($raised instanceof CertificateWasRefused) {
        expect($raised->endpoint())->toBe($endpoint)
            ->and($raised->presented())->toBe($peer->digest)
            ->and($raised->pinned())->toBe(A_PIN_NOTHING_PRESENTS);
    }
})->with([
    'a read' => ['/api/status', static fn(string $at): mixed => aPinnedClient($at, 'a-run-token', A_PIN_NOTHING_PRESENTS)->read('/api/status')],
    'a bundle' => [Api::bundle('t4m8'), static fn(string $at): mixed => aPinnedClient($at, 'a-run-token', A_PIN_NOTHING_PRESENTS)->bundle('t4m8')],
    'live updates' => [Api::EVENTS_ENDPOINT, static fn(string $at): mixed => aPinnedClient($at, 'a-run-token', A_PIN_NOTHING_PRESENTS)->eventSource()->open(null)],
    'the door' => [Admission::ENDPOINT, static fn(string $at): mixed => Admission::at($at, A_PIN_NOTHING_PRESENTS, aWait())->open('a-password')],
]);

it('reads a peer presenting the pinned certificate and answering nothing as silence', function (): void {
    $peer = TlsListener::start();

    expect(whatThePeerRaised(static fn(): mixed => aPinnedClient($peer->address(), 'a-run-token', $peer->digest)->read('/api/status')))
        ->toBeInstanceOf(Unreachable::class);
});

it('asks no certificate of an address that is not pinned', function (): void {
    $peer = TlsListener::start();

    expect(whatThePeerRaised(static fn(): mixed => aClientAt(sprintf('http://127.0.0.1:%d', $peer->port), 'a-run-token')->read('/api/status')))
        ->toBeInstanceOf(Unreachable::class);
});

it('reads the digest of the certificate a peer presents, as a pin is written', function (): void {
    $peer = TlsListener::start();

    expect(PresentedCertificate::at(BaseUrl::pinned($peer->address(), CertificatePin::fromSha256(A_PIN_NOTHING_PRESENTS)), A_PROBES_WAIT))
        ->toBe($peer->digest);
});

it('reads nothing, and connects to nothing, where the call has no wait left', function (): void {
    $peer = TlsListener::start();

    expect(PresentedCertificate::at(BaseUrl::pinned($peer->address(), CertificatePin::fromSha256(A_PIN_NOTHING_PRESENTS)), 0.0))
        ->toBeNull();
});

it('gives up on a peer that never answers the handshake once what is left of the wait is spent', function (): void {
    $peer = AnsweringListener::silent();
    $started = microtime(true);

    $presented = PresentedCertificate::at(BaseUrl::fromString($peer->address()), 0.3);

    expect($presented)->toBeNull()
        ->and(microtime(true) - $started)->toBeLessThan(2.0);
});

it('reads nothing where no encrypted connection could be set up', function (): void {
    $server = stream_socket_server('tcp://127.0.0.1:0');

    if ($server === false) {
        throw new RuntimeException('no port could be taken to let go of');
    }

    $name = (string) stream_socket_get_name($server, false);
    fclose($server);

    expect(PresentedCertificate::at(BaseUrl::onPort((int) substr($name, (int) strrpos($name, ':') + 1)), A_PROBES_WAIT))->toBeNull();
});

it('holds back the warning a connection that could not be set up raises', function (): void {
    error_clear_last();

    PresentedCertificate::at(BaseUrl::onPort(1), A_PROBES_WAIT);

    expect(error_get_last())->toBeNull();
});

it('puts back the error handler that was in place', function (): void {
    $before = static fn(): bool => false;
    set_error_handler($before);

    try {
        PresentedCertificate::at(BaseUrl::onPort(1), A_PROBES_WAIT);
    } finally {
        $after = set_error_handler(null);
        restore_error_handler();
        restore_error_handler();
    }

    expect($after)->toBe($before);
});
