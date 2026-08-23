<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Envelope;

use function array_key_exists;
use function is_array;
use function is_int;
use function is_string;
use function json_decode;

use JsonException;
use Lemonfiber\Sdk\Contract\Api;
use Lemonfiber\Sdk\Exception\ApiVersionMismatch;
use Lemonfiber\Sdk\Exception\UnreadableResponse;

/**
 * Turns an answer's text into an envelope, refusing one this client cannot read.
 *
 * Reading yields `Envelope<mixed>`, since only the generated types know which
 * shape a given `kind` carries. A caller narrows it through those.
 */
final readonly class EnvelopeReader
{
    private const int MAX_DEPTH = 64;

    public function __construct(private int $spokenVersion = Api::VERSION) {}

    /**
     * @return Envelope<mixed>
     *
     * @throws ApiVersionMismatch
     * @throws UnreadableResponse
     */
    public function read(string $body): Envelope
    {
        $decoded = $this->decode($body);

        if (! is_array($decoded)) {
            throw UnreadableResponse::notAnEnvelope();
        }

        $version = $decoded['api_version'] ?? null;

        if (! is_int($version)) {
            throw UnreadableResponse::versionMissing();
        }

        if ($version !== $this->spokenVersion) {
            throw ApiVersionMismatch::between($this->spokenVersion, $version);
        }

        $kind = $decoded['kind'] ?? null;

        if (! is_string($kind) || $kind === '') {
            throw UnreadableResponse::kindMissing();
        }

        if (! array_key_exists('data', $decoded)) {
            throw UnreadableResponse::dataMissing();
        }

        return new Envelope($version, $kind, $decoded['data']);
    }

    /**
     * @throws UnreadableResponse
     */
    private function decode(string $body): mixed
    {
        try {
            return json_decode($body, true, self::MAX_DEPTH, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw UnreadableResponse::notJson($exception->getMessage());
        }
    }
}
