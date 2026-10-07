<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Tests\Support;

use function basename;
use function bin2hex;
use function copy;
use function dirname;
use function escapeshellarg;
use function exec;
use function fclose;
use function file_put_contents;
use function glob;
use function is_string;
use function mkdir;
use function proc_close;
use function proc_open;
use function random_bytes;
use function stream_get_contents;

/**
 * A temporary tree holding this package's scripts and a contract given to it.
 *
 * The scripts resolve their paths from their own directory, so a copy of them
 * inside such a tree reads that tree's contract and writes into that tree.
 */
final class ContractTree
{
    public const string GENERATOR = '/scripts/contract-generate.php';

    /**
     * A tree whose contract is the single file holding the text given.
     */
    public static function withFile(string $contract): string
    {
        $tree = self::make();

        file_put_contents($tree . '/contract/web-api.contract.json', $contract);

        return $tree;
    }

    /**
     * What the generator said running in the tree, and how it exited.
     *
     * @return array{status: int, stderr: string}
     */
    public static function generate(string $tree): array
    {
        $pipes = [];
        $process = proc_open(
            [PHP_BINARY, $tree . self::GENERATOR],
            [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes,
        );

        if ($process === false) {
            return ['status' => -1, 'stderr' => 'The generator could not be started.'];
        }

        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        return ['status' => proc_close($process), 'stderr' => is_string($stderr) ? $stderr : ''];
    }

    public static function remove(string $tree): void
    {
        exec('rm -rf ' . escapeshellarg($tree));
    }

    private static function make(): string
    {
        $root = dirname(__DIR__, 2);
        $tree = $root . '/.contract-test-' . bin2hex(random_bytes(6));

        mkdir($tree . '/scripts', 0o755, true);
        mkdir($tree . '/contract', 0o755, true);

        $scripts = glob($root . '/scripts/*.php');

        foreach ($scripts === false ? [] : $scripts as $script) {
            copy($script, $tree . '/scripts/' . basename($script));
        }

        file_put_contents($tree . '/contract/VERSION', "v9.9.9\n");

        return $tree;
    }
}
