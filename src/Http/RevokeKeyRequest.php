<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Http;

use Lemonfiber\Sdk\Contract\Api;
use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\EnvelopeReader;
use Override;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;

/**
 * Revoking an integration key, as the request that asks for it.
 *
 * Sent once, as an action is: a revoke takes access away and cannot be undone,
 * so this client does not send it again on its own where nothing answered. Its body is
 * `dry_run` alone, true where the revocation is rehearsed.
 *
 * @implements ReadsItsAnswer<Envelope<mixed>>
 */
final class RevokeKeyRequest extends Request implements HasBody, ReadsItsAnswer
{
    use HasJsonBody;

    #[Override]
    protected Method $method = Method::DELETE;

    public function __construct(private readonly string $name, private readonly bool $dryRun = false) {}

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

    /**
     * @return array{dry_run: bool}
     */
    protected function defaultBody(): array
    {
        return ['dry_run' => $this->dryRun];
    }
}
