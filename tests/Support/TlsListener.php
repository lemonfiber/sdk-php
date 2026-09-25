<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Tests\Support;

use function explode;
use function fgets;
use function is_resource;
use function proc_open;
use function proc_terminate;

use RuntimeException;

use function trim;

/**
 * A peer on this machine that sets up an encrypted connection and answers nothing.
 *
 * It runs in a process of its own, presenting a certificate it signed itself
 * when it started, and closes every connection once the handshake is over.
 * What it presents is known to the test, so a pin can be given that matches it
 * or one that does not.
 */
final class TlsListener
{
    /** @var resource */
    private $process;

    private function __construct(mixed $process, public readonly int $port, public readonly string $digest)
    {
        if (! is_resource($process)) {
            throw new RuntimeException('the listener did not start');
        }

        $this->process = $process;
    }

    public function __destruct()
    {
        proc_terminate($this->process);
    }

    public static function start(): self
    {
        $pipes = [];
        $process = proc_open([PHP_BINARY, __DIR__ . '/tls-listener.php'], [1 => ['pipe', 'w']], $pipes);
        $said = trim((string) fgets($pipes[1]));
        [$port, $digest] = explode(' ', $said . ' ');

        if ($digest === '') {
            throw new RuntimeException('the listener said nothing of where it listens');
        }

        return new self($process, (int) $port, $digest);
    }

    /**
     * The address it listens at, as a pinned address is written.
     */
    public function address(): string
    {
        return 'https://127.0.0.1:' . $this->port;
    }
}
