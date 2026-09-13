<?php

declare(strict_types=1);

use GuzzleHttp\Handler\StreamHandler;
use GuzzleHttp\HandlerStack;
use Lemonfiber\Sdk\Http\BaseUrl;
use Lemonfiber\Sdk\Http\CertificatePin;
use Lemonfiber\Sdk\Http\LemonfiberConnector;
use Lemonfiber\Sdk\Http\RunToken;
use Saloon\Contracts\Sender;
use Saloon\Http\Senders\GuzzleSender;

const A_DIGEST = '86b25c676b761e9a398081373fec783c2bec970baa255370838aebb5c687841e';

function connectorPinnedTo(string $digest): LemonfiberConnector
{
    return new LemonfiberConnector(
        BaseUrl::pinned('https://192.168.1.42:9000', CertificatePin::fromSha256($digest)),
        RunToken::fromString('a-run-token'),
    );
}

function connectorOnThisMachine(): LemonfiberConnector
{
    return new LemonfiberConnector(BaseUrl::onPort(9000), RunToken::fromString('a-run-token'));
}

/**
 * The handler a sender's stack was built around, which the stack does not expose.
 */
function handlerBeneath(Sender $sender): mixed
{
    if (! $sender instanceof GuzzleSender) {
        return null;
    }

    return new ReflectionProperty(HandlerStack::class, 'handler')->getValue($sender->getHandlerStack());
}

it('hands the digest to the transport under the key the peer check reads', function (): void {
    expect(connectorPinnedTo(A_DIGEST)->config()->all())
        ->toBe([
            'verify' => false,
            'stream_context' => ['ssl' => ['peer_fingerprint' => ['sha256' => A_DIGEST]]],
        ]);
});

it('hands the transport nothing when the address is on this machine', function (): void {
    expect(connectorOnThisMachine()->config()->all())->toBe([]);
});

// The peer check is honoured by the stream handler and by no other, so a pinned
// address sent through the handler chosen by default would travel unpinned.
it('sends a pinned address through the handler that honours the check', function (): void {
    expect(handlerBeneath(connectorPinnedTo(A_DIGEST)->sender()))
        ->toBeInstanceOf(StreamHandler::class);
});

it('leaves an address on this machine with the handler chosen by default', function (): void {
    expect(handlerBeneath(connectorOnThisMachine()->sender()))
        ->not->toBeInstanceOf(StreamHandler::class);
});

it('keeps the address it was pinned at', function (): void {
    expect(connectorPinnedTo(A_DIGEST)->resolveBaseUrl())->toBe('https://192.168.1.42:9000');
});
