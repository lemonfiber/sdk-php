<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Http;

/**
 * Turns a host name into the addresses it points at.
 */
interface HostResolver
{
    /**
     * The addresses `$host` resolves to, empty if it resolves to none.
     *
     * @return list<string>
     */
    public function addressesFor(string $host): array;
}
