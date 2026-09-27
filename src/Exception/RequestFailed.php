<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Exception;

use function is_array;
use function is_string;

use Lemonfiber\Sdk\Envelope\EnvelopeReader;
use Lemonfiber\Sdk\Generated\Kind;
use Lemonfiber\Sdk\Refusal;

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
        private readonly ?Refusal $refusal,
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
        $words = trim($body);
        $problem = self::problemIn($words);
        $said = self::saidIn($words, $problem);

        return new self($endpoint, $status, $said, Refusal::from($problem), $said ?? sprintf(
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
     * The whole problem document lemonfiber refused with, or none where the
     * answer was not one this client can read.
     *
     * Held apart from the message on purpose. A refusal's `detail` quotes what
     * a service itself said, with the secrets lemonfiber recognises withheld,
     * and recognising them is best effort: it is fit to show to the person who
     * asked and not to forward. The message, which loggers and reporters take
     * by default, carries the one plain sentence and nothing else.
     */
    public function refusal(): ?Refusal
    {
        return $this->refusal;
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
    private static function saidIn(string $words, mixed $problem): ?string
    {
        if ($words === '') {
            return null;
        }

        if (self::opensAStructure($words)) {
            return self::sentenceIn($problem);
        }

        return $words;
    }

    /**
     * The payload of the `error` envelope a body is, or nothing where it is
     * not one.
     *
     * The body is read as every other answer is read, so one this client cannot
     * read yields nothing. The kind names the payload without proving its shape.
     */
    private static function problemIn(string $words): mixed
    {
        try {
            $envelope = new EnvelopeReader()->read($words);
        } catch (Problem) {
            return null;
        }

        return $envelope->kind === Kind::Error->value ? $envelope->data : null;
    }

    /**
     * Whether a body opens something other than a sentence.
     */
    private static function opensAStructure(string $words): bool
    {
        return preg_match(self::OPENS_A_STRUCTURE, $words) === 1;
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
