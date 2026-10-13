<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk;

use SensitiveParameter;

/**
 * What proves the operator for one mint: their password given again, or a registered device
 * key's signature over a challenge the stack issued for that mint.
 */
final readonly class MintProof
{
    /**
     * @param array<string, string> $fields
     */
    private function __construct(private array $fields) {}

    /**
     * What is said of a proof: nothing it carries.
     *
     * @return array<string, never>
     */
    public function __debugInfo(): array
    {
        return [];
    }

    /**
     * The operator's password, given again.
     */
    public static function password(#[SensitiveParameter] string $password): self
    {
        return new self(['password' => $password]);
    }

    /**
     * The device key named `$device`'s signature over {@see KeyMint::signedText()} for
     * `$challenge`: ECDSA P-256 over SHA-256, in DER, as the phone's hardware made it.
     */
    public static function device(
        string $device,
        #[SensitiveParameter]
        string $challenge,
        #[SensitiveParameter]
        string $signature,
    ): self {
        return new self([
            'device' => $device,
            'challenge' => $challenge,
            'signature' => Base64Url::encoded($signature),
        ]);
    }

    /**
     * The proof's fields, as the request body carries them.
     *
     * @return array<string, string>
     */
    public function fields(): array
    {
        return $this->fields;
    }
}
