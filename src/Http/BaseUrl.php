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
 * An address on the machine lemonfiber runs on, and nowhere else (C6-R1, C6-R14).
 */
final readonly class BaseUrl
{
    private const int LOWEST_PORT = 1;

    private const int HIGHEST_PORT = 65535;

    private const string IPV4_LOOPBACK_PREFIX = '127.';

    private const string IPV6_LOOPBACK = '::1';

    /**
     * @var list<string>
     */
    private const array FORBIDDEN_PARTS = ['user', 'pass', 'query', 'fragment'];

    private function __construct(private string $value) {}

    /**
     * @throws ConfigurationProblem
     */
    public static function onPort(int $port): self
    {
        if ($port < self::LOWEST_PORT || $port > self::HIGHEST_PORT) {
            throw ConfigurationProblem::portOutOfRange($port);
        }

        return new self('http://127.0.0.1:' . $port);
    }

    /**
     * @throws ConfigurationProblem
     */
    public static function fromString(string $address, ?HostResolver $resolver = null): self
    {
        $parts = parse_url($address);

        if ($parts === false) {
            throw ConfigurationProblem::unreadableAddress($address);
        }

        $scheme = $parts['scheme'] ?? '';

        if ($scheme !== 'http' && $scheme !== 'https') {
            throw ConfigurationProblem::unsupportedScheme($scheme);
        }

        if (array_intersect(self::FORBIDDEN_PARTS, array_keys($parts)) !== []) {
            throw ConfigurationProblem::addressCarriesExtras();
        }

        $host = $parts['host'] ?? '';

        if (! self::isOnThisMachine($host, $resolver ?? new SystemHostResolver())) {
            throw ConfigurationProblem::addressIsNotOnThisMachine($host);
        }

        $port = $parts['port'] ?? null;

        $authority = $port === null ? $host : $host . ':' . $port;

        return new self($scheme . '://' . $authority . rtrim($parts['path'] ?? '', '/'));
    }

    /**
     * The address as a string.
     */
    public function toString(): string
    {
        return $this->value;
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
