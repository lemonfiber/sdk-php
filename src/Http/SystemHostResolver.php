<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Http;

use function gethostbynamel;
use function is_array;

/**
 * Resolves through the machine's own resolver, so `/etc/hosts` is consulted.
 *
 * IPv4 only: a name reaches this client because an operator or the binary
 * printed it, and a loopback name carries an `A` record on every platform
 * lemonfiber supports. A literal `::1` is matched without resolving.
 */
final readonly class SystemHostResolver implements HostResolver
{
    /**
     * @return list<string>
     */
    public function addressesFor(string $host): array
    {
        $found = gethostbynamel($host);

        return is_array($found) ? $found : [];
    }
}
