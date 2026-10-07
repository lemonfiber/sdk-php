<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Http;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\EnvelopeReader;
use Override;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;

/**
 * A request that acts, mirroring a command.
 *
 * The key naming the attempt is held per request rather than on the
 * connection. One client sends many actions, and a key set once for all of
 * them would tell a stack that every one of them was a re-send of the first.
 *
 * **Sent once, key or no key.** A connection that broke may have carried the
 * action and lost its answer, and the stack does not read the key, so a second
 * sending would be a second change. Where nothing answered, the call raises
 * {@see \Lemonfiber\Sdk\Exception\Unreachable} and the caller decides.
 *
 * @implements ReadsItsAnswer<Envelope<mixed>>
 */
final class ActionRequest extends Request implements HasBody, ReadsItsAnswer
{
    use HasJsonBody;

    #[Override]
    protected Method $method = Method::POST;

    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(
        private readonly string $endpoint,
        private readonly array $payload = [],
        private readonly ?IdempotencyKey $attempt = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return $this->endpoint;
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
        return $this->payload;
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
