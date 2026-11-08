<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Http;

use Lemonfiber\Sdk\Contract\Api;
use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\EnvelopeReader;
use Lemonfiber\Sdk\PushEndpointRegistering;
use Override;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;
use SensitiveParameter;

/**
 * Registering a push endpoint, or removing one, as the request that asks for it.
 *
 * Each is sent once: a registration hands the stack a secret to encrypt to, and a removal
 * stops a phone being woken.
 *
 * @implements ReadsItsAnswer<Envelope<mixed>>
 */
final class PushEndpointRequest extends Request implements HasBody, ReadsItsAnswer
{
    use HasJsonBody;

    /**
     * @param array<string, string> $fields
     */
    private function __construct(
        Method $method,
        private readonly string $endpoint,
        #[SensitiveParameter]
        private readonly array $fields = [],
    ) {
        $this->method = $method;
    }

    /**
     * What is said of the request: where it goes, and nothing it carries.
     *
     * @return array{endpoint: string}
     */
    public function __debugInfo(): array
    {
        return ['endpoint' => $this->endpoint];
    }

    /**
     * Register `$registering`.
     */
    public static function registering(PushEndpointRegistering $registering): self
    {
        return new self(Method::POST, Api::PUSH_ENDPOINTS_ENDPOINT, $registering->fields());
    }

    /**
     * Remove the push endpoint `$name`.
     */
    public static function removing(string $name): self
    {
        return new self(Method::DELETE, Api::pushEndpoint($name));
    }

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
     * @return array<string, string>
     */
    protected function defaultBody(): array
    {
        return $this->fields;
    }
}
