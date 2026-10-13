<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Http;

use Lemonfiber\Sdk\Contract\Api;
use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\EnvelopeReader;
use Lemonfiber\Sdk\KeyMint;
use Lemonfiber\Sdk\MintProof;
use Override;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;

/**
 * Minting an integration key, as the request that asks for it, with the operator's proof.
 *
 * Sent once: a mint that reached the stack and lost its answer minted a key, and a second
 * send would mint another, or spend a challenge that was already spent.
 *
 * @implements ReadsItsAnswer<Envelope<mixed>>
 */
final class MintKeyRequest extends Request implements HasBody, ReadsItsAnswer
{
    use HasJsonBody;

    #[Override]
    protected Method $method = Method::POST;

    public function __construct(
        private readonly KeyMint $mint,
        private readonly MintProof $proof,
    ) {}

    public function resolveEndpoint(): string
    {
        return Api::KEYS_ENDPOINT;
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
     * @return array<string, string|bool>
     */
    protected function defaultBody(): array
    {
        return [...$this->mint->fields(), ...$this->proof->fields()];
    }
}
