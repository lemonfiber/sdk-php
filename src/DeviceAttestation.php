<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk;

use function array_map;

/**
 * The platform's attestation of where a device key lives and which app made it.
 */
final readonly class DeviceAttestation
{
    /**
     * @param array{platform: string, chain?: list<string>, object?: string} $fields
     */
    private function __construct(private array $fields) {}

    /**
     * An Android key attestation: each certificate's DER, the key's own first.
     *
     * @param list<string> $chain
     */
    public static function android(array $chain): self
    {
        return new self([
            'platform' => 'android',
            'chain' => array_map(Base64Url::encoded(...), $chain),
        ]);
    }

    /**
     * An iOS App Attest attestation object's CBOR, made with the SHA-256 of the public key's
     * `SubjectPublicKeyInfo` DER, hashed once, as its client data hash.
     */
    public static function ios(string $object): self
    {
        return new self(['platform' => 'ios', 'object' => Base64Url::encoded($object)]);
    }

    /**
     * The attestation as the request body carries it.
     *
     * @return array{platform: string, chain?: list<string>, object?: string}
     */
    public function fields(): array
    {
        return $this->fields;
    }
}
