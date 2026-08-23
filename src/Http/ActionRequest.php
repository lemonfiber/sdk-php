<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Http;

use Override;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

/**
 * A request that acts, mirroring a command (ARCH-R48).
 */
final class ActionRequest extends Request implements HasBody
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
    ) {}

    public function resolveEndpoint(): string
    {
        return $this->endpoint;
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultBody(): array
    {
        return $this->payload;
    }
}
