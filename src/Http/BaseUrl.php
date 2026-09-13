<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Http;

use function array_all;
use function array_intersect;
use function array_keys;
use function filter_var;
use function inet_pton;

use Lemonfiber\Sdk\Exception\ConfigurationProblem;

use function parse_url;
use function rtrim;
use function str_starts_with;
use function trim;

/**
 * An address on the machine lemonfiber runs on, or one a certificate pin vouches for.
 */
final readonly class BaseUrl
{
    private const int LOWEST_PORT = 1;

    private const int HIGHEST_PORT = 65535;

    private const string IPV4_LOOPBACK_PREFIX = '127.';

    private const string IPV6_LOOPBACK = '::1';

    private const string ENCRYPTED_SCHEME = 'https';

    /**
     * @var list<string>
     */
    private const array FORBIDDEN_PARTS = ['user', 'pass', 'query', 'fragment'];

    private function __construct(private string $value, private ?CertificatePin $pin) {}

    /**
     * @throws ConfigurationProblem
     */
    public static function onPort(int $port): self
    {
        if ($port < self::LOWEST_PORT || $port > self::HIGHEST_PORT) {
            throw ConfigurationProblem::portOutOfRange($port);
        }

        return new self('http://127.0.0.1:' . $port, null);
    }

    /**
     * An address on this machine, which needs no pin and accepts none.
     *
     * @throws ConfigurationProblem
     */
    public static function fromString(string $address, ?HostResolver $resolver = null): self
    {
        $parts = self::partsOf($address);

        if (! self::isOnThisMachine(self::hostIn($parts), $resolver ?? new SystemHostResolver())) {
            throw ConfigurationProblem::addressIsNotOnThisMachine(self::hostIn($parts));
        }

        return new self(self::assemble($parts), null);
    }

    /**
     * An address anywhere, held to the one certificate the pin names.
     *
     * @throws ConfigurationProblem
     */
    public static function pinned(string $address, CertificatePin $pin): self
    {
        $parts = self::partsOf($address);
        $scheme = self::schemeIn($parts);

        if ($scheme !== self::ENCRYPTED_SCHEME) {
            throw ConfigurationProblem::pinnedAddressIsNotEncrypted($scheme);
        }

        return new self(self::assemble($parts), $pin);
    }

    /**
     * The address as a string.
     */
    public function toString(): string
    {
        return $this->value;
    }

    /**
     * The certificate this address is held to, where it is held to one.
     */
    public function pin(): ?CertificatePin
    {
        return $this->pin;
    }

    /**
     * @return array{scheme?: string, host?: string, port?: int, user?: string, pass?: string, path?: string, query?: string, fragment?: string}
     *
     * @throws ConfigurationProblem
     */
    private static function partsOf(string $address): array
    {
        $parts = parse_url($address);

        if ($parts === false) {
            throw ConfigurationProblem::unreadableAddress($address);
        }

        $scheme = self::schemeIn($parts);

        if ($scheme !== 'http' && $scheme !== self::ENCRYPTED_SCHEME) {
            throw ConfigurationProblem::unsupportedScheme($scheme);
        }

        if (array_intersect(self::FORBIDDEN_PARTS, array_keys($parts)) !== []) {
            throw ConfigurationProblem::addressCarriesExtras();
        }

        return $parts;
    }

    /**
     * @param  array{scheme?: string, host?: string, port?: int, user?: string, pass?: string, path?: string, query?: string, fragment?: string}  $parts
     */
    private static function hostIn(array $parts): string
    {
        return $parts['host'] ?? '';
    }

    /**
     * @param  array{scheme?: string, host?: string, port?: int, user?: string, pass?: string, path?: string, query?: string, fragment?: string}  $parts
     */
    private static function schemeIn(array $parts): string
    {
        return $parts['scheme'] ?? '';
    }

    /**
     * @param  array{scheme?: string, host?: string, port?: int, user?: string, pass?: string, path?: string, query?: string, fragment?: string}  $parts
     */
    private static function assemble(array $parts): string
    {
        $host = self::hostIn($parts);
        $port = $parts['port'] ?? null;
        $authority = $port === null ? $host : $host . ':' . $port;

        return self::schemeIn($parts) . '://' . $authority . rtrim($parts['path'] ?? '', '/');
    }

    private static function isOnThisMachine(string $host, HostResolver $resolver): bool
    {
        $bare = trim($host, '[]');

        if (self::isLiteralAddress($bare)) {
            return self::isLoopbackAddress($bare);
        }

        $addresses = $resolver->addressesFor($bare);

        if ($addresses === []) {
            return false;
        }
        return array_all($addresses, fn(string $address): bool => self::isLoopbackAddress($address));
    }

    private static function isLiteralAddress(string $host): bool
    {
        return filter_var($host, FILTER_VALIDATE_IP) !== false;
    }

    private static function isLoopbackAddress(string $address): bool
    {
        if (filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false) {
            return str_starts_with($address, self::IPV4_LOOPBACK_PREFIX);
        }

        return inet_pton($address) === inet_pton(self::IPV6_LOOPBACK);
    }
}
