<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Scripts;

require_once __DIR__ . '/ContractVendor.php';
require_once __DIR__ . '/References.php';
require_once __DIR__ . '/ShapePlan.php';
require_once __DIR__ . '/Tarball.php';
require_once __DIR__ . '/VendoredContract.php';

use function array_slice;
use function curl_errno;
use function curl_error;
use function curl_exec;
use function curl_getinfo;
use function curl_init;
use function curl_setopt_array;
use function dirname;
use function fwrite;
use function is_array;
use function is_string;
use function preg_match;
use function sprintf;

use UnexpectedValueException;

/**
 * Fetches the contract artefact at one revision of lemonfiber and vendors it
 * beside the revision it came from.
 *
 * The only step in this package that reaches the network. Generation reads the
 * vendored copy, so a build never does.
 *
 * A revision is a release tag or a full commit hash. Both name one artefact,
 * so the vendored bytes can be checked against what that revision served. The
 * revision is fetched as the archive of its tree, from the host that serves it
 * rather than through a redirect to it, and `ContractVendor` writes whichever
 * layout the revision holds the contract in.
 *
 * Usage: `composer contract:sync -- v1.0.0`
 *        `composer contract:sync -- d2bf74b950a9f6fb73f2bcd60e2d8adf85337cd6`
 */
final readonly class ContractSync
{
    private const string ARCHIVE = 'https://codeload.github.com/lemonfiber/lemonfiber/tar.gz/%s';

    private const string RELEASE_TAG = '/^v\d+\.\d+\.\d+$/';

    /**
     * An abbreviated hash is refused: it names one artefact today and may not later.
     */
    private const string COMMIT = '/^[0-9a-f]{40}$/';

    private const int TIMEOUT_SECONDS = 60;

    /**
     * The answer a revision that exists comes back with.
     */
    private const int SERVED = 200;

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

        $archive = $this->fetch($revision);

        if ($archive === null) {
            return 1;
        }

        try {
            $vendored = new ContractVendor($this->root)->vendor($archive, $revision);
        } catch (UnexpectedValueException $refused) {
            return $this->refuse($refused->getMessage() . ' Nothing was vendored.');
        }

        echo ContractVendor::summary($revision, $vendored);

        return 0;
    }

    /**
     * The archive of the revision's tree, or nothing when there is none to fetch.
     */
    private function fetch(string $revision): ?string
    {
        $handle = curl_init();

        curl_setopt_array($handle, [
            CURLOPT_URL => sprintf(self::ARCHIVE, $revision),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_TIMEOUT => self::TIMEOUT_SECONDS,
            CURLOPT_USERAGENT => 'lemonfiber-sdk-php contract:sync',
        ]);

        $body = curl_exec($handle);
        $status = curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
        $failure = curl_errno($handle) === 0 ? '' : curl_error($handle);

        if ($failure !== '' || ! is_string($body)) {
            $this->refuse(sprintf('lemonfiber %s could not be fetched. %s', $revision, $failure));

            return null;
        }

        if ($status !== self::SERVED) {
            $this->refuse(sprintf('lemonfiber %s could not be fetched (the answer came back %d).', $revision, $status));

            return null;
        }

        return $body;
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
