<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Http;

use Lemonfiber\Sdk\ActionRequest;
use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\EnvelopeReader;
use Override;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;

/**
 * An action, as the request that asks for it.
 *
 * The key naming the attempt is held per request rather than on the
 * connection. One client sends many actions, and a key set once for all of
 * them would tell a stack that every one of them was a re-send of the first.
 *
 * **Sent once, key or no key.** A connection that broke may have carried the
 * action and lost its answer, so this client never sends one again on its own.
 * Where nothing answered, the call raises
 * {@see \Lemonfiber\Sdk\Exception\Unreachable} and the caller decides.
 *
 * @implements ReadsItsAnswer<Envelope<mixed>>
 */
final class ActRequest extends Request implements HasBody, ReadsItsAnswer
{
    use HasJsonBody;

    #[Override]
    protected Method $method = Method::POST;

    public function __construct(
        private readonly ActionRequest $action,
        private readonly ?IdempotencyKey $attempt = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return $this->action->endpoint();
    }

    /**
     * @return Envelope<mixed>
     */
    #[Override]
    public function createDtoFromResponse(Response $response): Envelope
    {
        return new EnvelopeReader()->read($response->body());
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultBody(): array
    {
        return $this->action->payload();
    }

    /**
     * The headers this request carries of its own, which is the key or none.
     *
     * @return array<string, string>
     */
    protected function defaultHeaders(): array
    {
        return $this->attempt?->header() ?? [];
    }
}
