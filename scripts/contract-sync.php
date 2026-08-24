<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Scripts;

use function array_keys;
use function array_slice;
use function count;
use function curl_errno;
use function curl_error;
use function curl_exec;
use function curl_getinfo;
use function curl_init;
use function curl_setopt_array;
use function dirname;
use function file_put_contents;
use function fwrite;
use function implode;
use function is_array;
use function is_dir;
use function is_int;
use function is_string;
use function json_decode;

use JsonException;

use function mkdir;
use function preg_match;
use function sprintf;
use function str_ends_with;

/**
 * Fetches the contract artefact at one revision of lemonfiber and vendors it
 * beside the revision it came from.
 *
 * The only step in this package that reaches the network. Generation reads the
 * vendored copy, so a build never does.
 *
 * A revision is a release tag or a full commit hash. Both name one artefact,
 * so the vendored bytes can be checked against what that revision served.
 *
 * Usage: `composer contract:sync -- v1.0.0`
 *        `composer contract:sync -- d2bf74b950a9f6fb73f2bcd60e2d8adf85337cd6`
 */
final readonly class ContractSync
{
    private const string ASSET = 'https://raw.githubusercontent.com/lemonfiber/lemonfiber/%s/contract/web-api.contract.json';

    private const string RELEASE_TAG = '/^v\d+\.\d+\.\d+$/';

    /**
     * An abbreviated hash is refused: it names one artefact today and may not later.
     */
    private const string COMMIT = '/^[0-9a-f]{40}$/';

    private const string ARTEFACT = 'contract/web-api.contract.json';

    private const string STAMP = 'contract/VERSION';

    private const int TIMEOUT_SECONDS = 30;

    /**
     * The answer a revision with an artefact comes back with.
     */
    private const int SERVED = 200;

    private const int MAX_DEPTH = 64;

    public function __construct(private string $root) {}

    /**
     * @param  list<string>  $arguments
     */
    public function run(array $arguments): int
    {
        $revision = $arguments[0] ?? '';

        if (preg_match(self::RELEASE_TAG, $revision) !== 1 && preg_match(self::COMMIT, $revision) !== 1) {
            return $this->refuse('contract:sync needs a release tag or a full 40-character commit hash, as in `composer contract:sync -- v1.0.0`.');
        }

        $artefact = $this->artefact($revision);

        return $artefact === null ? 1 : $this->vendor($revision, $artefact);
    }

    /**
     * What a revision serves together with what it describes, or nothing when
     * the revision serves no artefact.
     *
     * @return array{served: string, version: int, kinds: list<string>}|null
     */
    private function artefact(string $revision): ?array
    {
        $served = $this->fetch($revision);

        if ($served === null) {
            return null;
        }

        $described = $this->described($served, $revision);

        return $described === null ? null : [
            'served' => $served,
            'version' => $described['version'],
            'kinds' => $described['kinds'],
        ];
    }

    /**
     * What the revision served, or nothing when it served no artefact.
     */
    private function fetch(string $revision): ?string
    {
        $handle = curl_init();

        curl_setopt_array($handle, [
            CURLOPT_URL => sprintf(self::ASSET, $revision),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_TIMEOUT => self::TIMEOUT_SECONDS,
            CURLOPT_USERAGENT => 'lemonfiber-sdk-php contract:sync',
        ]);

        $body = curl_exec($handle);
        $status = curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
        $failure = curl_errno($handle) === 0 ? '' : curl_error($handle);

        if ($failure !== '' || ! is_string($body)) {
            $this->refuse(sprintf('The contract artefact for %s could not be fetched. %s', $revision, $failure));

            return null;
        }

        if ($status !== self::SERVED) {
            $this->refuse(sprintf('lemonfiber %s serves no contract artefact (the answer came back %d).', $revision, $status));

            return null;
        }

        return $body;
    }

    /**
     * What the served text describes, or nothing when it is not an artefact.
     *
     * @return array{version: int, kinds: list<string>}|null
     */
    private function described(string $served, string $revision): ?array
    {
        try {
            $decoded = json_decode($served, true, self::MAX_DEPTH, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            $this->refuse(sprintf('What %s served is not JSON: %s', $revision, $exception->getMessage()));

            return null;
        }

        $version = is_array($decoded) ? $decoded['api_version'] ?? null : null;
        $kinds = is_array($decoded) ? $decoded['kinds'] ?? null : null;

        if (! is_int($version) || ! is_array($kinds)) {
            $this->refuse(sprintf('What %s served is not a contract artefact.', $revision));

            return null;
        }

        if ($kinds === []) {
            $this->refuse(sprintf('The contract %s served describes no kinds.', $revision));

            return null;
        }

        $named = [];

        foreach (array_keys($kinds) as $kind) {
            if (! is_string($kind)) {
                $this->refuse(sprintf('The contract %s served names a kind that is not a word.', $revision));

                return null;
            }

            $named[] = $kind;
        }

        return ['version' => $version, 'kinds' => $named];
    }

    /**
     * @param  array{served: string, version: int, kinds: list<string>}  $artefact
     */
    private function vendor(string $revision, array $artefact): int
    {
        $directory = dirname($this->root . '/' . self::ARTEFACT);

        if (! is_dir($directory)) {
            mkdir($directory, 0o755, true);
        }

        $served = $artefact['served'];

        file_put_contents($this->root . '/' . self::ARTEFACT, str_ends_with($served, "\n") ? $served : $served . "\n");
        file_put_contents($this->root . '/' . self::STAMP, $revision . "\n");

        echo sprintf(
            "contract: vendored %s, api_version %d, %d kinds\n  %s\nNow run `composer contract:generate` and commit both.\n",
            $revision,
            $artefact['version'],
            count($artefact['kinds']),
            implode(', ', $artefact['kinds']),
        );

        return 0;
    }

    private function refuse(string $message): int
    {
        fwrite(STDERR, 'contract: ' . $message . "\n");

        return 1;
    }
}

$argv = $_SERVER['argv'] ?? [];
$given = [];

foreach (is_array($argv) ? array_slice($argv, 1) : [] as $argument) {
    $given[] = is_string($argument) ? $argument : '';
}

exit(new ContractSync(dirname(__DIR__))->run($given));
