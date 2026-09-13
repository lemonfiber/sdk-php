<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Http;

use Lemonfiber\Sdk\Exception\ConfigurationProblem;

use function preg_match;
use function strtolower;
use function trim;

/**
 * The digest of the one certificate a stack is permitted to present.
 */
final readonly class CertificatePin
{
    private const string SHA256_HEX = '/^[0-9a-f]{64}$/';

    private function __construct(private string $value) {}

    /**
     * A pin taken from pairing material, as SHA-256 over the certificate's DER encoding.
     *
     * @throws ConfigurationProblem
     */
    public static function fromSha256(string $digest): self
    {
        $normalised = strtolower(trim($digest));

        if (preg_match(self::SHA256_HEX, $normalised) !== 1) {
            throw ConfigurationProblem::pinIsNotACertificateDigest($digest);
        }

        return new self($normalised);
    }

    /**
     * The digest, as the transport is given it.
     */
    public function toString(): string
    {
        return $this->value;
    }
}
