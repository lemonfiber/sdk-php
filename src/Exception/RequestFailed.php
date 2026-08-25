<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Exception;

use function is_array;
use function is_string;

use Lemonfiber\Sdk\Envelope\EnvelopeReader;
use Lemonfiber\Sdk\Generated\Kind;

use function preg_match;

use RuntimeException;

use function sprintf;
use function trim;

/**
 * lemonfiber turned the request down.
 */
final class RequestFailed extends RuntimeException implements Problem
{
    /**
     * A body that opens something other than a sentence: an envelope, or markup
     * from whatever stands between the caller and lemonfiber.
     */
    private const string OPENS_A_STRUCTURE = '/^[<\[{]/';

    private function __construct(
        private readonly string $endpoint,
        private readonly int $status,
        private readonly ?string $said,
        string $message,
    ) {
        parent::__construct($message);
    }

    /**
     * The refusal, carrying whatever lemonfiber wrote in the body it answered
     * with. A body holding no sentence this client can read leaves the message
     * naming the endpoint and the status instead.
     */
    public static function from(string $endpoint, int $status, string $body): self
    {
        $said = self::saidIn($body);

        return new self($endpoint, $status, $said, $said ?? sprintf(
            'lemonfiber turned down the request for %s and answered %d. Nothing was taken from that answer.',
            $endpoint,
            $status,
        ));
    }

    /**
     * The endpoint that was asked.
     */
    public function endpoint(): string
    {
        return $this->endpoint;
    }

    /**
     * The status lemonfiber answered with.
     */
    public function status(): int
    {
        return $this->status;
    }

    /**
     * The sentence lemonfiber refused with, or nothing where the answer carried
     * none.
     */
    public function said(): ?string
    {
        return $this->said;
    }

    /**
     * The sentence a refusal's body carries.
     *
     * Two shapes arrive. An action lemonfiber does not offer, or an argument it
     * does not know, is answered in prose. A command that ran and failed is
     * answered with an `error` envelope, whose summary is that same one
     * sentence. A body of any other shape did not come from lemonfiber and is
     * not handed on as its words.
     */
    private static function saidIn(string $body): ?string
    {
        $words = trim($body);

        if ($words === '') {
            return null;
        }

        if (preg_match(self::OPENS_A_STRUCTURE, $words) === 1) {
            return self::summaryIn($words);
        }

        return $words;
    }

    /**
     * The one plain sentence an `error` envelope carries.
     *
     * The body is read as every other answer is read, so one this client cannot
     * read yields nothing. The kind names the payload without proving its shape.
     */
    private static function summaryIn(string $body): ?string
    {
        try {
            $envelope = new EnvelopeReader()->read($body);
        } catch (Problem) {
            return null;
        }

        return $envelope->kind === Kind::Error->value ? self::sentenceIn($envelope->data) : null;
    }

    /**
     * The sentence an `error` payload holds as its summary.
     *
     * A payload of another shape, a summary that is not written out, and a
     * summary of nothing all yield nothing.
     */
    private static function sentenceIn(mixed $data): ?string
    {
        $summary = is_array($data) ? ($data['summary'] ?? null) : null;

        if (! is_string($summary)) {
            return null;
        }

        $sentence = trim($summary);

        return $sentence === '' ? null : $sentence;
    }
}
