<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk;

use SensitiveParameter;

/**
 * A phone's UnifiedPush endpoint to register: what to call it, the `https` address its
 * distributor gave, and the WebPush public key and secret the stack encrypts to.
 */
final readonly class PushEndpointRegistering
{
    private function __construct(
        public string $name,
        private string $endpoint,
        private string $p256dh,
        #[SensitiveParameter]
        private string $auth,
    ) {}

    /**
     * What is said of the registration: its name, and nothing it carries.
     *
     * @return array{name: string}
     */
    public function __debugInfo(): array
    {
        return ['name' => $this->name];
    }

    /**
     * The address `$endpoint` registered as `$name`, encrypted to the WebPush public key
     * `$p256dh`, an uncompressed P-256 point, and the WebPush secret `$auth`, both keys given
     * as bytes.
     */
    public static function of(
        string $name,
        string $endpoint,
        string $p256dh,
        #[SensitiveParameter]
        string $auth,
    ): self {
        return new self($name, $endpoint, $p256dh, $auth);
    }

    /**
     * The body the stack reads the registration from, the keys in base64url.
     *
     * @return array{name: string, endpoint: string, p256dh: string, auth: string}
     */
    public function fields(): array
    {
        return [
            'name' => $this->name,
            'endpoint' => $this->endpoint,
            'p256dh' => Base64Url::encoded($this->p256dh),
            'auth' => Base64Url::encoded($this->auth),
        ];
    }
}
