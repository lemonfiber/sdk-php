<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Http;

use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Handler\StreamHandler;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Promise\Utils;
use GuzzleHttp\RequestOptions;
use Lemonfiber\Sdk\Exception\ConfigurationProblem;
use LogicException;
use Psr\Http\Message\UriInterface;
use Saloon\Contracts\Sender;
use Saloon\Data\FactoryCollection;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Http\PendingRequest;
use Saloon\Http\Response;
use Saloon\Http\Senders\GuzzleSender;

use function sprintf;

/**
 * The one way a request reaches the wire: to the stack's own address, never
 * onward from it, and through the pin where the address carries one.
 *
 * A request is checked as the transport is about to be handed it, once
 * everything that may shape a request has run. One addressed anywhere but the
 * stack's own scheme, host and port is refused unsent. Of the options a request
 * asks for, only whether to stream the answer is read; every other option is
 * this sender's, so no request turns the pin off, swaps the handler that
 * honours it, or follows an answer somewhere else.
 *
 * An answer pointing elsewhere is not followed, and is raised as the stack not
 * answering: what was asked for did not come back from the stack.
 *
 * **Each attempt is bounded by what is left of its call's wait.** A request
 * names no wait of its own, so none can wait longer than the call it belongs
 * to. An answer that is read in full is held to the whole of what is left; a
 * streamed one only while it is being reached, since a stream held open is
 * read at the pace its reader asks for. That bound on reaching a stream holds
 * on an address held to a pin; over plain HTTP the transport's own bounds
 * apply to a stream. An attempt with nothing left is not made, and is raised
 * as nothing answering.
 */
final readonly class StackSender implements Sender
{
    /** What an attempt the call had no time left for reports. */
    private const string OUT_OF_TIME = 'The call used all the time it was given before this attempt could be made.';

    /**
     * The transport, which nothing outside this sender holds.
     */
    private GuzzleSender $transport;

    /**
     * The peer check every request is sent with, where the address carries a pin.
     *
     * @var array{}|array{verify: false, stream_context: array{ssl: array{peer_fingerprint: array{sha256: string}}}}
     */
    private array $peerCheck;

    public function __construct(private BaseUrl $baseUrl)
    {
        $pin = $baseUrl->pin();

        $this->transport = new GuzzleSender();

        if ($pin instanceof CertificatePin) {
            $this->transport->getHandlerStack()->setHandler(new StreamHandler());
        }

        $this->peerCheck = $this->heldTo($pin);
    }

    public function getFactoryCollection(): FactoryCollection
    {
        return $this->transport->getFactoryCollection();
    }

    /**
     * @throws ConfigurationProblem
     * @throws FatalRequestException
     * @throws LogicException where the request belongs to no call
     */
    public function send(PendingRequest $pendingRequest): Response
    {
        $request = $pendingRequest->createPsrRequest();
        $uri = $request->getUri();

        if (! $this->baseUrl->isOriginOf((string) $uri)) {
            throw ConfigurationProblem::requestLeavesTheStack($this->originIn($uri));
        }

        $left = Call::of($pendingRequest)->secondsLeft();

        if ($left <= 0.0) {
            throw new FatalRequestException(new TransferException(self::OUT_OF_TIME, $request), $pendingRequest);
        }

        $streamed = $pendingRequest->config()->get(RequestOptions::STREAM) === true;

        try {
            $answer = $this->transport->getGuzzleClient()->send($request, [
                RequestOptions::ALLOW_REDIRECTS => false,
                RequestOptions::HTTP_ERRORS => false,
                RequestOptions::STREAM => $streamed,
                ...($streamed ? [RequestOptions::READ_TIMEOUT => $left] : [RequestOptions::TIMEOUT => $left]),
                ...$this->peerCheck,
            ]);
        } catch (TransferException $nothingAnswered) {
            throw new FatalRequestException($nothingAnswered, $pendingRequest);
        }

        $response = $pendingRequest->getResponseClass()::fromPsrResponse($answer, $pendingRequest, $request);

        if ($response->redirect()) {
            throw new FatalRequestException(new TransferException(sprintf(
                'The answer was a redirect (%d), and a redirect is not followed.',
                $response->status(),
            ), $request), $pendingRequest);
        }

        return $response;
    }

    public function sendAsync(PendingRequest $pendingRequest): PromiseInterface
    {
        return Utils::task(fn(): Response => $this->send($pendingRequest));
    }

    /**
     * The peer check, where the address carries a pin.
     *
     * The digest is compared while the connection is being set up, so a peer
     * that does not match is dropped before a request carrying the token is
     * written to it. It decides the peer's identity in place of the platform
     * trust store, which holds no opinion about a certificate a stack signed
     * itself.
     *
     * @return array{}|array{verify: false, stream_context: array{ssl: array{peer_fingerprint: array{sha256: string}}}}
     */
    private function heldTo(?CertificatePin $pin): array
    {
        if (! $pin instanceof CertificatePin) {
            return [];
        }

        return [
            RequestOptions::VERIFY => false,
            RequestOptions::STREAM_CONTEXT => ['ssl' => ['peer_fingerprint' => ['sha256' => $pin->toString()]]],
        ];
    }

    /**
     * An address as its scheme, host and port, and nothing else.
     */
    private function originIn(UriInterface $uri): string
    {
        return (string) $uri->withUserInfo('')->withPath('')->withQuery('')->withFragment('');
    }
}
