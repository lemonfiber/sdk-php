<?php

declare(strict_types=1);

use GuzzleHttp\Handler\StreamHandler;
use GuzzleHttp\HandlerStack;
use Lemonfiber\Sdk\Http\BaseUrl;
use Lemonfiber\Sdk\Http\CertificatePin;
use Lemonfiber\Sdk\Http\LemonfiberConnector;
use Lemonfiber\Sdk\Http\RunToken;
use Lemonfiber\Sdk\Http\StackSender;
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
 * The handler beneath the transport a connector sends through, which neither exposes.
 */
function handlerBeneath(LemonfiberConnector $connector): mixed
{
    $sender = $connector->sender();

    if (! $sender instanceof StackSender) {
        return null;
    }

    $transport = new ReflectionProperty(StackSender::class, 'transport')->getValue($sender);

    if (! $transport instanceof GuzzleSender) {
        return null;
    }

    return new ReflectionProperty(HandlerStack::class, 'handler')->getValue($transport->getHandlerStack());
}

it('sends every request through the sender that holds it to the stack', function (): void {
    expect(connectorPinnedTo(A_DIGEST)->sender())->toBeInstanceOf(StackSender::class)
        ->and(connectorOnThisMachine()->sender())->toBeInstanceOf(StackSender::class);
});

// The peer check is honoured by the stream handler and by no other, so a pinned
// address sent through the handler chosen by default would travel unpinned.
it('sends a pinned address through the handler that honours the check', function (): void {
    expect(handlerBeneath(connectorPinnedTo(A_DIGEST)))
        ->toBeInstanceOf(StreamHandler::class);
});

it('leaves an address on this machine with the handler chosen by default', function (): void {
    $handler = handlerBeneath(connectorOnThisMachine());

    expect($handler)->not->toBeNull()
        ->and($handler)->not->toBeInstanceOf(StreamHandler::class);
});

it('keeps the address it was pinned at, and the pin', function (): void {
    $connector = connectorPinnedTo(A_DIGEST);

    expect($connector->resolveBaseUrl())->toBe('https://192.168.1.42:9000')
        ->and($connector->baseUrl()->pin()?->toString())->toBe(A_DIGEST);
});
