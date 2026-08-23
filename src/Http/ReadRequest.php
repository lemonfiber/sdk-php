<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Http;

use Override;
use Saloon\Enums\Method;
use Saloon\Http\Request;

/**
 * A request that reads, mirroring a command's machine-readable output.
 */
final class ReadRequest extends Request
{
    #[Override]
    protected Method $method = Method::GET;

    /**
     * @param array<string, scalar|null> $parameters
     */
    public function __construct(
        private readonly string $endpoint,
        private readonly array $parameters = [],
    ) {}

    public function resolveEndpoint(): string
    {
        return $this->endpoint;
    }

    /**
     * @return array<string, scalar|null>
     */
    protected function defaultQuery(): array
    {
        return $this->parameters;
    }
}
