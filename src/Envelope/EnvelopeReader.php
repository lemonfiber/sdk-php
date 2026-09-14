<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Envelope;

use function array_key_exists;
use function explode;
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
 *
 * Two shapes of body, one rule for a line of it. Nearly every answer is a
 * single envelope; the logs are a document a line, and each of those documents
 * is an envelope answering to everything the one is held to — so
 * {@see self::readEach()} is where the body is cut and {@see self::read()}
 * stays the only place a version is checked or a kind is required.
 */
final readonly class EnvelopeReader
{
    private const int MAX_DEPTH = 64;

    private const string LINE = "\n";

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
     * A body of one document a line, as an envelope a line.
     *
     * Lines with nothing on them are passed over. A rendering ends every
     * document with a break, so the last one leaves an empty line behind it,
     * and a body carrying no documents at all is an empty body rather than a
     * shape of its own — both come out as no envelopes rather than as an
     * answer this client cannot read.
     *
     * @return list<Envelope<mixed>>
     *
     * @throws ApiVersionMismatch
     * @throws UnreadableResponse
     */
    public function readEach(string $body): array
    {
        $envelopes = [];

        foreach (explode(self::LINE, $body) as $line) {
            if ($line !== '') {
                $envelopes[] = $this->read($line);
            }
        }

        return $envelopes;
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
