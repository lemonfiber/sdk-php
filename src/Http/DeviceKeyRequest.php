<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Http;

use Lemonfiber\Sdk\Base64Url;
use Lemonfiber\Sdk\Contract\Api;
use Lemonfiber\Sdk\DeviceAttestation;
use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\EnvelopeReader;
use Override;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;
use SensitiveParameter;

/**
 * Registering a device key, asking a challenge for one, or revoking one, as the request that
 * asks for it.
 *
 * Each is sent once: a registration or a revoke changes what proves the operator, and a
 * challenge asked again spends the one asked before.
 *
 * @implements ReadsItsAnswer<Envelope<mixed>>
 */
final class DeviceKeyRequest extends Request implements HasBody, ReadsItsAnswer
{
    use HasJsonBody;

    /**
     * @param array<string, mixed> $fields
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
     * Register the public half `$publicKey`, its `SubjectPublicKeyInfo` DER, as the device key
     * `$name`, attested by `$attestation`, with the operator's password.
     */
    public static function registering(
        string $name,
        string $publicKey,
        DeviceAttestation $attestation,
        #[SensitiveParameter]
        string $password,
    ): self {
        return new self(Method::POST, Api::DEVICE_KEYS_ENDPOINT, [
            'name' => $name,
            'public_key' => Base64Url::encoded($publicKey),
            'attestation' => $attestation->fields(),
            'password' => $password,
        ]);
    }

    /**
     * Ask a challenge for the device key `$name`.
     */
    public static function challenging(string $name): self
    {
        return new self(Method::POST, Api::deviceKeyChallenge($name));
    }

    /**
     * Revoke the device key `$name`.
     */
    public static function revoking(string $name): self
    {
        return new self(Method::DELETE, Api::deviceKey($name));
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
     * @return array<string, mixed>
     */
    protected function defaultBody(): array
    {
        return $this->fields;
    }
}
