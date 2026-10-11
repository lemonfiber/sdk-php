<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Http;

use Lemonfiber\Sdk\Contract\Api;
use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\EnvelopeReader;
use Override;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

/**
 * Revoking an integration key, as the request that asks for it.
 *
 * Sent once, as an action is: a revoke takes access away and cannot be undone,
 * so this client does not send it again on its own where nothing answered.
 *
 * @implements ReadsItsAnswer<Envelope<mixed>>
 */
final class RevokeKeyRequest extends Request implements ReadsItsAnswer
{
    #[Override]
    protected Method $method = Method::DELETE;

    public function __construct(private readonly string $name) {}

    public function resolveEndpoint(): string
    {
        return Api::key($this->name);
    }

    /**
     * @return Envelope<mixed>
     */
    #[Override]
    public function createDtoFromResponse(Response $response): Envelope
    {
        return new EnvelopeReader()->read($response->body());
    }
}
