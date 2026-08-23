<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Http;

use Lemonfiber\Sdk\Contract\Api;
use Saloon\Contracts\Authenticator;
use Saloon\Http\Connector;

/**
 * The transport: one loopback address, one token, sent as a header.
 */
final class LemonfiberConnector extends Connector
{
    public function __construct(
        private readonly BaseUrl $baseUrl,
        private readonly RunToken $token,
    ) {}

    public function resolveBaseUrl(): string
    {
        return $this->baseUrl->toString();
    }

    protected function defaultAuth(): Authenticator
    {
        return $this->token;
    }

    /**
     * @return array<string, string>
     */
    protected function defaultHeaders(): array
    {
        return ['Accept' => Api::JSON_MEDIA_TYPE];
    }
}
