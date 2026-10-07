<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Exception;

use function in_array;
use function is_array;
use function is_string;

use Lemonfiber\Sdk\Envelope\EnvelopeReader;
use Lemonfiber\Sdk\Generated\Kind;
use Lemonfiber\Sdk\Generated\RefusalCode;
use Lemonfiber\Sdk\Refusal;

use function preg_match;

use RuntimeException;

use function sprintf;
use function trim;

/**
 * lemonfiber turned the request down.
 *
 * Every refusal is one of its families, chosen by the status it was answered
 * with and, where the request was turned away, by its code: {@see NotAdmitted},
 * {@see Declined}, {@see Misasked}, {@see Missing}, {@see Busy},
 * {@see TooManyAttempts} and {@see Failed}. A caller catching this catches
 * every one of them.
 */
abstract class RequestFailed extends RuntimeException implements Problem
{
    /**
     * A body that opens something other than a sentence: an envelope, or markup
     * from whatever stands between the caller and lemonfiber.
     */
    private const string OPENS_A_STRUCTURE = '/^[<\[{]/';

    /** The statuses a request is turned away with, for who is asking or from where. */
    private const array TURNED_AWAY = [401, 403];

    private const int MISASKED = 400;

    private const int MISSING = 404;

    private const int BUSY = 409;

    private const int TOO_MANY = 429;

    protected function __construct(
        private readonly string $endpoint,
        private readonly int $status,
        private readonly ?string $said,
        private readonly ?Refusal $refusal,
        string $message,
    ) {
        parent::__construct($message);
    }

    /**
     * The refusal, as the family its status and code make it, carrying whatever
     * lemonfiber wrote in the body it answered with. A body holding no sentence
     * this client can read leaves the message naming the endpoint and the status
     * instead.
     *
     * A request turned away is {@see NotAdmitted} where the credential itself
     * was refused: its code says so, it carries no code this client knows, or
     * it carries no sentence. Turned away with any other code is
     * {@see Declined}. `$retryAfter` is the wait a `Retry-After` header named,
     * which only {@see TooManyAttempts} carries.
     */
    final public static function from(string $endpoint, int $status, string $body, ?int $retryAfter = null): self
    {
        $words = trim($body);
        $problem = self::problemIn($words);
        $said = self::saidIn($words, $problem);
        $refusal = Refusal::from($problem);
        $message = $said ?? sprintf(
            'lemonfiber turned down the request for %s and answered %d. Nothing was taken from that answer.',
            $endpoint,
            $status,
        );

        return match (true) {
            $status === self::TOO_MANY => new TooManyAttempts($endpoint, $status, $said, $refusal, $retryAfter),
            in_array($status, self::TURNED_AWAY, true) => self::turnedAway($endpoint, $status, $said, $refusal, $message),
            $status === self::MISASKED => new Misasked($endpoint, $status, $said, $refusal, $message),
            $status === self::MISSING => new Missing($endpoint, $status, $said, $refusal, $message),
            $status === self::BUSY => new Busy($endpoint, $status, $said, $refusal, $message),
            default => new Failed($endpoint, $status, $said, $refusal, $message),
        };
    }

    /**
     * The endpoint that was asked.
     */
    final public function endpoint(): string
    {
        return $this->endpoint;
    }

    /**
     * The status lemonfiber answered with.
     */
    final public function status(): int
    {
        return $this->status;
    }

    /**
     * The sentence lemonfiber refused with, or nothing where the answer carried
     * none.
     */
    final public function said(): ?string
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
    final public function refusal(): ?Refusal
    {
        return $this->refusal;
    }

    /**
     * Why lemonfiber refused, as the code its problem document carries.
     *
     * None where the answer carried no problem document this client can read,
     * and none where it carried a code the generated list does not name: a
     * server newer than this package refuses with codes this package has not
     * heard of. A refusal with no code here is read by its `status()` alone.
     * What a refusal means is read from its code, never from `said()`, which
     * is written for a person and may be reworded.
     */
    final public function code(): ?RefusalCode
    {
        return RefusalCode::of($this->refusal?->code());
    }

    /**
     * The family of a request turned away: the credential, or something else.
     */
    private static function turnedAway(string $endpoint, int $status, ?string $said, ?Refusal $refusal, string $message): self
    {
        $code = RefusalCode::of($refusal?->code());

        return !$code instanceof RefusalCode || $code === RefusalCode::NotAdmitted || $said === null
            ? new NotAdmitted($endpoint, $status, $said, $refusal, $message)
            : new Declined($endpoint, $status, $said, $refusal, $message);
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
