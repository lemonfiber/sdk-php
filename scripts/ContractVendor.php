<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Scripts;

use function array_key_exists;
use function array_keys;
use function bin2hex;
use function count;
use function dirname;
use function file_put_contents;
use function implode;
use function is_array;
use function is_dir;
use function is_file;
use function is_int;
use function is_string;
use function json_decode;

use JsonException;

use function mkdir;
use function random_bytes;
use function rename;
use function rmdir;
use function scandir;
use function sprintf;
use function str_ends_with;

use UnexpectedValueException;

use function unlink;

/**
 * Writes the contract one revision of lemonfiber holds under `contract/`, beside the revision.
 *
 * The revision arrives as the archive of its tree, so whichever layout it holds
 * the contract in is what is vendored: the directory `contract/web-api/` where
 * it has an index, and the single file otherwise. Vendoring one layout removes
 * the other, and the directory is replaced whole, so a file the revision no
 * longer holds leaves with it.
 */
final readonly class ContractVendor
{
    /**
     * Where the single file sits in the archive, relative to `contract/`.
     */
    private const string FILE_IN_CONTRACT = 'web-api.contract.json';

    private const string CONTRACT = 'contract';

    public function __construct(private string $root, private Tarball $tarball = new Tarball()) {}

    /**
     * Vendors the contract the archive holds and records the revision it came from.
     *
     * @return array{layout: string, version: int, kinds: list<string>} what was vendored
     *
     * @throws UnexpectedValueException naming what is wrong, where the archive holds no contract this can vendor
     */
    public function vendor(string $archive, string $revision): array
    {
        $directory = $this->tarball->filesUnder($archive, VendoredContract::DIRECTORY);

        if ($directory !== []) {
            return $this->vendorDirectory($directory, $revision);
        }

        $served = $this->tarball->filesUnder($archive, self::CONTRACT)[self::FILE_IN_CONTRACT] ?? null;

        if ($served === null) {
            throw new UnexpectedValueException(sprintf(
                'lemonfiber %s holds neither %s/ nor %s.',
                $revision,
                VendoredContract::DIRECTORY,
                VendoredContract::FILE,
            ));
        }

        $described = $this->described($this->object($served, VendoredContract::FILE, $revision), VendoredContract::FILE, $revision);
        $this->remove($this->root . '/' . VendoredContract::DIRECTORY);
        $this->write($this->root . '/' . VendoredContract::FILE, str_ends_with($served, "\n") ? $served : $served . "\n");
        $this->write($this->root . '/' . VendoredContract::STAMP, $revision . "\n");

        return ['layout' => VendoredContract::FILE] + $described;
    }

    /**
     * How many kinds were vendored, for a reader.
     *
     * @param  array{layout: string, version: int, kinds: list<string>}  $vendored
     */
    public static function summary(string $revision, array $vendored): string
    {
        return sprintf(
            "contract: vendored %s as %s, api_version %d, %d kinds\n  %s\nNow run `composer contract:generate` and commit both.\n",
            $revision,
            $vendored['layout'],
            $vendored['version'],
            count($vendored['kinds']),
            implode(', ', $vendored['kinds']),
        );
    }

    /**
     * @param  array<string, string>  $files  path relative to the directory to contents
     * @return array{layout: string, version: int, kinds: list<string>}
     *
     * @throws UnexpectedValueException
     */
    private function vendorDirectory(array $files, string $revision): array
    {
        $named = VendoredContract::DIRECTORY . '/' . VendoredContract::INDEX;

        if (! array_key_exists(VendoredContract::INDEX, $files)) {
            throw new UnexpectedValueException(sprintf('lemonfiber %s holds %s/ with no %s in it.', $revision, VendoredContract::DIRECTORY, VendoredContract::INDEX));
        }

        $index = $this->object($files[VendoredContract::INDEX], $named, $revision);
        $described = $this->described($index, $named, $revision);
        $kinds = is_array($index['kinds'] ?? null) ? $index['kinds'] : [];

        foreach ([...$kinds, ...$this->lists($index)] as $path) {
            if (! is_string($path) || ! array_key_exists($path, $files)) {
                throw new UnexpectedValueException(sprintf(
                    '%s at %s names %s, which the revision does not hold under %s/.',
                    $named,
                    $revision,
                    References::written($path),
                    VendoredContract::DIRECTORY,
                ));
            }
        }

        foreach ($files as $path => $contents) {
            $this->json($contents, VendoredContract::DIRECTORY . '/' . $path, $revision);
        }

        $staging = $this->root . '/' . self::CONTRACT . '/.web-api-' . bin2hex(random_bytes(6));

        foreach ($files as $path => $contents) {
            $this->write($staging . '/' . $path, $contents);
        }

        $this->remove($this->root . '/' . VendoredContract::DIRECTORY);

        if (! rename($staging, $this->root . '/' . VendoredContract::DIRECTORY)) {
            throw new UnexpectedValueException(sprintf('%s/ could not be put in place.', VendoredContract::DIRECTORY));
        }

        $this->remove($this->root . '/' . VendoredContract::FILE);
        $this->write($this->root . '/' . VendoredContract::STAMP, $revision . "\n");

        return ['layout' => VendoredContract::DIRECTORY . '/'] + $described;
    }

    /**
     * The files the index names for its lists.
     *
     * @param  array<mixed, mixed>  $index
     * @return list<mixed>
     */
    private function lists(array $index): array
    {
        $named = [];

        foreach (VendoredContract::LISTS as $list) {
            if (array_key_exists($list, $index)) {
                $named[] = $index[$list];
            }
        }

        return $named;
    }

    /**
     * The version and the kinds a contract names, checked as far as vendoring needs.
     *
     * @param  array<mixed, mixed>  $contract
     * @return array{version: int, kinds: list<string>}
     *
     * @throws UnexpectedValueException
     */
    private function described(array $contract, string $named, string $revision): array
    {
        $version = $contract['api_version'] ?? null;
        $kinds = $contract['kinds'] ?? null;

        if (! is_int($version) || ! is_array($kinds)) {
            throw new UnexpectedValueException(sprintf('%s at %s is not a contract artefact.', $named, $revision));
        }

        if ($kinds === []) {
            throw new UnexpectedValueException(sprintf('%s at %s describes no kinds.', $named, $revision));
        }

        $names = [];

        foreach (array_keys($kinds) as $kind) {
            if (! is_string($kind)) {
                throw new UnexpectedValueException(sprintf('%s at %s names a kind that is not a word.', $named, $revision));
            }

            $names[] = $kind;
        }

        return ['version' => $version, 'kinds' => $names];
    }

    /**
     * What a served file holds, which must be a JSON object.
     *
     * @return array<mixed, mixed>
     *
     * @throws UnexpectedValueException
     */
    private function object(string $served, string $named, string $revision): array
    {
        $decoded = $this->json($served, $named, $revision);

        if (! is_array($decoded)) {
            throw new UnexpectedValueException(sprintf('%s at %s is not a contract artefact.', $named, $revision));
        }

        return $decoded;
    }

    /**
     * What a served file holds, which must be JSON.
     *
     * @throws UnexpectedValueException
     */
    private function json(string $served, string $named, string $revision): mixed
    {
        try {
            return json_decode($served, true, VendoredContract::MAX_DEPTH, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new UnexpectedValueException(sprintf('%s at %s is not JSON: %s', $named, $revision, $exception->getMessage()), 0, $exception);
        }
    }

    /**
     * @throws UnexpectedValueException
     */
    private function write(string $target, string $contents): void
    {
        $directory = dirname($target);

        if (! is_dir($directory) && ! mkdir($directory, 0o755, true) && ! is_dir($directory)) {
            throw new UnexpectedValueException(sprintf('%s could not be made.', $directory));
        }

        if (file_put_contents($target, $contents) === false) {
            throw new UnexpectedValueException(sprintf('%s could not be written.', $target));
        }
    }

    /**
     * Removes a file, or a directory and everything in it, where there is one.
     */
    private function remove(string $path): void
    {
        if (is_file($path)) {
            unlink($path);

            return;
        }

        if (! is_dir($path)) {
            return;
        }

        $entries = scandir($path);

        foreach ($entries === false ? [] : $entries as $entry) {
            if ($entry !== '.' && $entry !== '..') {
                $this->remove($path . '/' . $entry);
            }
        }

        rmdir($path);
    }
}
