<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Tests\Support;

use function explode;
use function fgets;
use function file_get_contents;
use function is_file;
use function is_resource;
use function is_string;
use function proc_open;
use function proc_terminate;

use RuntimeException;

use function sprintf;
use function sys_get_temp_dir;
use function tempnam;
use function trim;
use function unlink;

/**
 * A peer on this machine that answers every request the same way, and keeps
 * the head of every request it was sent.
 *
 * It runs in a process of its own, over plain TCP or over an encrypted
 * connection presenting a certificate it signed itself when it started. What
 * it heard is what a test reads to know whether a request, and the token in
 * it, ever reached it.
 */
final class AnsweringListener
{
    /**
     * An envelope any read accepts, so a request that reached this peer comes back answered.
     */
    public const string AN_ENVELOPE = '{"api_version":1,"kind":"status","data":{"health":"healthy"}}';

    /** @var resource */
    private $process;

    private function __construct(
        mixed $process,
        private readonly string $heard,
        private readonly string $scheme,
        private readonly string $host,
        public readonly int $port,
        public readonly string $digest,
    ) {
        if (! is_resource($process)) {
            throw new RuntimeException('the listener did not start');
        }

        $this->process = $process;
    }

    public function __destruct()
    {
        proc_terminate($this->process);
        if (is_file($this->heard)) {
            unlink($this->heard);
        }
    }

    /**
     * A peer over plain TCP, answering with the status, the address it points to, and the body given.
     */
    public static function plain(string $host = '127.0.0.1', int $status = 200, string $location = '-', string $body = self::AN_ENVELOPE): self
    {
        return self::start('tcp', 'http', $host, $status, $location, $body);
    }

    /**
     * A peer over an encrypted connection, answering with the status, the address it points to, and the body given.
     */
    public static function encrypted(int $status = 200, string $location = '-', string $body = self::AN_ENVELOPE): self
    {
        return self::start('tls', 'https', '127.0.0.1', $status, $location, $body);
    }

    /**
     * A peer that hears every request and answers none, over plain TCP or, encrypted, presenting its own certificate.
     */
    public static function silent(bool $encrypted = false): self
    {
        return $encrypted
            ? self::start('tls', 'https', '127.0.0.1', 0, '-', '')
            : self::start('tcp', 'http', '127.0.0.1', 0, '-', '');
    }

    /**
     * A peer that hears every request and hangs up on it without answering.
     */
    public static function hangingUp(): self
    {
        return self::start('tcp', 'http', '127.0.0.1', 1, '-', '');
    }

    /**
     * A peer that answers with a picture stating no length, sends that many bytes of it, and then holds the connection open.
     */
    public static function streamingAPicture(int $bytes): self
    {
        return self::start('tcp', 'http', '127.0.0.1', 2, '-', (string) $bytes);
    }

    /**
     * Where it listens.
     */
    public function address(): string
    {
        return sprintf('%s://%s:%d', $this->scheme, $this->host, $this->port);
    }

    /**
     * The head of every request it was sent, one after another.
     */
    public function heard(): string
    {
        $said = file_get_contents($this->heard);

        return is_string($said) ? $said : '';
    }

    private static function start(string $mode, string $scheme, string $host, int $status, string $location, string $body): self
    {
        $heard = tempnam(sys_get_temp_dir(), 'heard');

        if ($heard === false) {
            throw new RuntimeException('nowhere to keep what the listener hears');
        }

        $pipes = [];
        $process = proc_open(
            [PHP_BINARY, __DIR__ . '/answering-listener.php', $mode, $host, $heard, (string) $status, $location, $body],
            [1 => ['pipe', 'w']],
            $pipes,
        );
        $said = trim((string) fgets($pipes[1]));
        [$port, $digest] = explode(' ', $said . ' ');

        if ($digest === '') {
            throw new RuntimeException('the listener said nothing of where it listens');
        }

        return new self($process, $heard, $scheme, $host, (int) $port, $digest);
    }
}
