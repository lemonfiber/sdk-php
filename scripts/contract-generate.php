<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Scripts;

require_once __DIR__ . '/SchemaTypes.php';

use function array_key_exists;
use function count;
use function dirname;
use function file_get_contents;
use function file_put_contents;
use function fwrite;
use function glob;
use function is_array;
use function is_dir;
use function is_file;
use function is_int;
use function is_string;
use function json_decode;

use JsonException;

use function ksort;
use function mkdir;
use function preg_match;
use function preg_split;
use function sprintf;
use function strtolower;
use function trim;
use function ucfirst;
use function unlink;

/**
 * Writes this package's contract types from the vendored artefact.
 *
 * Offline and deterministic: the same artefact in, the same files out, so CI
 * regenerates and fails on any difference.
 *
 * Each kind's schema is the whole envelope and carries the title `Envelope`,
 * which is the Rust type's name. The class is named from the kind instead, so
 * every kind is a class of its own rather than one more collision.
 *
 * Spec: 20-architecture/contracts/web-api.md
 */
final readonly class ContractGenerator
{
    /**
     * The `api_version` this package implements.
     */
    private const int SPOKEN_VERSION = 1;

    private const string ARTEFACT = 'contract/web-api.contract.json';

    private const string STAMP = 'contract/VERSION';

    private const string OUTPUT = 'src/Generated';

    private const string NAMESPACE = 'Lemonfiber\\Sdk\\Generated';

    private const int MAX_DEPTH = 64;

    public function __construct(
        private string $root,
        private SchemaTypes $types = new SchemaTypes(),
    ) {}

    public function run(): int
    {
        $artefact = $this->vendored();

        if ($artefact === null) {
            return 1;
        }

        $version = $artefact['api_version'] ?? null;

        if (! is_int($version)) {
            return $this->refuse('The vendored contract names no whole-number api_version, so it is not a contract artefact.');
        }

        if ($version !== self::SPOKEN_VERSION) {
            return $this->refuse(sprintf(
                'The vendored contract is api_version %d and this package implements %d. Nothing was generated. Sync a revision this package speaks, or implement %d first.',
                $version,
                self::SPOKEN_VERSION,
                $version,
            ));
        }

        $kinds = $artefact['kinds'] ?? null;

        if (! is_array($kinds)) {
            return $this->refuse('The vendored contract names no kinds at all, so it is not a contract artefact.');
        }

        if ($kinds === []) {
            return $this->refuse('The vendored contract describes no kinds.');
        }

        ksort($kinds);

        return $this->emit($kinds, $version);
    }

    /**
     * The vendored artefact, or nothing when it cannot be read.
     *
     * @return array<mixed, mixed>|null
     */
    private function vendored(): ?array
    {
        $text = $this->read();

        return $text === null ? null : $this->decoded($text);
    }

    /**
     * The vendored file's text, or nothing when there is none to read.
     */
    private function read(): ?string
    {
        $path = $this->root . '/' . self::ARTEFACT;

        if (! is_file($path)) {
            $this->refuse('There is no vendored contract at ' . self::ARTEFACT . '. Run `composer contract:sync -- <tag>` first.');

            return null;
        }

        $text = file_get_contents($path);

        if ($text === false) {
            $this->refuse(self::ARTEFACT . ' could not be read.');

            return null;
        }

        return $text;
    }

    /**
     * What the vendored text describes, or nothing when it is not an artefact.
     *
     * @return array<mixed, mixed>|null
     */
    private function decoded(string $text): ?array
    {
        try {
            $decoded = json_decode($text, true, self::MAX_DEPTH, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            $this->refuse(self::ARTEFACT . ' is not JSON: ' . $exception->getMessage());

            return null;
        }

        if (! is_array($decoded)) {
            $this->refuse(self::ARTEFACT . ' is not a contract artefact.');

            return null;
        }

        return $decoded;
    }

    /**
     * @param  array<mixed, mixed>  $kinds
     */
    private function emit(array $kinds, int $version): int
    {
        $stamp = $this->stamp();
        $named = [];
        $files = [];

        foreach ($kinds as $kind => $schema) {
            if (! is_string($kind) || ! is_array($schema)) {
                return $this->refuse('The vendored contract holds a kind that is not a named schema.');
            }

            $name = $this->pascal($kind);

            if ($name === null) {
                return $this->refuse(sprintf('The kind `%s` cannot be named as a PHP class.', $kind));
            }

            if (array_key_exists($name, $named)) {
                return $this->refuse(sprintf('The kinds `%s` and `%s` would both be named %s.', $named[$name], $kind, $name));
            }

            $named[$name] = $kind;
            $files[self::OUTPUT . '/' . $name . 'Envelope.php'] = $this->envelopeClass($kind, $name, $schema, $stamp, $version);
        }

        $files[self::OUTPUT . '/Kind.php'] = $this->kindEnum($named, $stamp, $version);
        $files[self::OUTPUT . '/Contract.php'] = $this->contractClass($stamp, $version);

        $directory = $this->root . '/' . self::OUTPUT;

        if (! is_dir($directory) && ! mkdir($directory, 0o755, true) && ! is_dir($directory)) {
            return $this->refuse(sprintf('%s could not be made, so nothing was generated.', self::OUTPUT));
        }

        $this->clear();

        foreach ($files as $path => $contents) {
            if (file_put_contents($this->root . '/' . $path, $contents) === false) {
                return $this->refuse(sprintf('%s could not be written, so what is on disk is now incomplete.', $path));
            }
        }

        echo sprintf("contract: %d kinds generated from %s into %s\n", count($named), $stamp, self::OUTPUT);

        return 0;
    }

    /**
     * The revision the vendored artefact was taken from.
     */
    private function stamp(): string
    {
        $text = file_get_contents($this->root . '/' . self::STAMP);

        return $text === false ? 'an unrecorded revision' : trim($text);
    }

    /**
     * Removes what an earlier generation wrote, so a kind that is gone leaves.
     */
    private function clear(): void
    {
        $found = glob($this->root . '/' . self::OUTPUT . '/*.php');

        foreach ($found === false ? [] : $found as $path) {
            unlink($path);
        }
    }

    /**
     * @param  array<mixed, mixed>  $schema
     */
    private function envelopeClass(string $kind, string $name, array $schema, string $stamp, int $version): string
    {
        $properties = $schema['properties'] ?? null;
        $payload = is_array($properties) ? $properties['data'] ?? null : null;
        $defs = $schema['$defs'] ?? null;
        $named = is_array($defs) ? $defs : [];

        $type = is_array($payload)
            ? $this->types->typeOf($payload, $named)
            : SchemaTypes::UNKNOWN;

        return $this->header($stamp, $version) . sprintf(
            <<<'PHP'

                use Lemonfiber\Sdk\Envelope\Envelope;
                use Lemonfiber\Sdk\Envelope\Payload;
                use Lemonfiber\Sdk\Exception\UnexpectedKind;

                /**
                 * The `%s` envelope, shaped as the contract describes it.
                 *
                 * @phpstan-type Data %s
                 */
                final class %sEnvelope
                {
                    /**
                     * The kind an envelope must carry to be read as this one.
                     */
                    public const Kind KIND = Kind::%s;

                    /**
                     * The same envelope with its payload typed by its kind.
                     *
                     * @param  Envelope<mixed>  $envelope
                     * @return Envelope<Data>
                     *
                     * @throws UnexpectedKind
                     */
                    public static function in(Envelope $envelope): Envelope
                    {
                        /** @var Data $data */
                        $data = Payload::under(self::KIND, $envelope);

                        return new Envelope($envelope->apiVersion, $envelope->kind, $data);
                    }
                }

                PHP,
            $kind,
            $type,
            $name,
            $name,
        );
    }

    /**
     * @param  array<string, string>  $named  class name to the kind it came from
     */
    private function kindEnum(array $named, string $stamp, int $version): string
    {
        $cases = '';

        foreach ($named as $name => $kind) {
            $cases .= sprintf("    case %s = %s;\n", $name, $this->types->quoted($kind));
        }

        return $this->header($stamp, $version) . sprintf(
            <<<'PHP'

                /**
                 * Every kind the contract describes, and the only ones this package reads.
                 */
                enum Kind: string
                {
                %s}

                PHP,
            $cases,
        );
    }

    private function contractClass(string $stamp, int $version): string
    {
        return $this->header($stamp, $version) . sprintf(
            <<<'PHP'

                /**
                 * What the vendored contract artefact states about itself.
                 */
                final class Contract
                {
                    /**
                     * The wire version these types were generated from.
                     */
                    public const int API_VERSION = %d;

                    /**
                     * The lemonfiber revision the artefact was vendored from.
                     */
                    public const string SOURCE = %s;
                }

                PHP,
            $version,
            $this->types->quoted($stamp),
        );
    }

    private function header(string $stamp, int $version): string
    {
        return sprintf(
            <<<'PHP'
                <?php

                // Generated from %s. Do not edit.
                // Source: %s, api_version %d.
                // Regenerate with `composer contract:generate`.

                declare(strict_types=1);

                namespace %s;

                PHP,
            self::ARTEFACT,
            $stamp,
            $version,
            self::NAMESPACE,
        );
    }


    /**
     * The class name a kind is written under, or nothing when it has none.
     */
    private function pascal(string $kind): ?string
    {
        $parts = preg_split('/[^A-Za-z0-9]+/', $kind);
        $name = '';

        foreach ($parts === false ? [] : $parts as $part) {
            $name .= ucfirst($part);
        }

        if (preg_match('/^[A-Za-z][A-Za-z0-9]*$/', $name) !== 1 || strtolower($name) === 'class') {
            return null;
        }

        return ucfirst($name);
    }

    private function refuse(string $message): int
    {
        fwrite(STDERR, 'contract: ' . $message . "\n");

        return 1;
    }
}

exit(new ContractGenerator(dirname(__DIR__))->run());
