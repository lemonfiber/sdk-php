<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk;

use function in_array;
use function is_array;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Exception\UnreadableResponse;
use Lemonfiber\Sdk\Generated\EndedEnvelope;
use Lemonfiber\Sdk\Generated\Kind;

/**
 * Where the work one name stands for got to.
 *
 * An action that reaches the services runs for minutes, so it is not waited
 * for: the request is answered with a name, and the name is redeemed
 * afterwards for whatever became of the work. This is what redeeming one says.
 *
 * Three states, and the kind tells them apart:
 *
 * | status | kind | where it got to |
 * |---|---|---|
 * | `202` | `job` | still going |
 * | `200` | the command's own | finished, and this is what it came to |
 * | `200` | `ended` | ended before it finished, and why |
 *
 * Work that ended was released by name, let go for want of anybody asking, or
 * followed services that stopped saying anything. It is neither of the other
 * two, so {@see self::answering()} takes an arm for each of the three, has no
 * default, and hands the ended arm which of those it was.
 *
 * A failure is not one of the three. Work that stopped on a problem answers
 * with the `error` envelope at the status that problem warrants, which is a
 * refusal and arrives as {@see Exception\RequestFailed} carrying the sentence
 * lemonfiber wrote; a name this run never handed out arrives as
 * {@see Exception\NoSuchJob}.
 *
 * The action the name was for is not carried here. It is on the `job` envelope
 * {@see Client::repair()} already answered with, which is where a caller holds
 * it.
 *
 * @phpstan-import-type JobEnding from EndedEnvelope
 */
final readonly class JobStanding
{
    /**
     * The status work still going is answered under.
     */
    private const int STILL_GOING = 202;

    /**
     * Every reason work ends before it finished for.
     */
    private const array ENDINGS = ['released', 'let-go', 'quiet'];

    /**
     * @param Envelope<mixed>|null $outcome
     * @param JobEnding|null $ending
     */
    private function __construct(
        public string $job,
        private ?Envelope $outcome,
        private ?string $ending,
    ) {}

    /**
     * The standing an answer describes.
     *
     * @param Envelope<mixed> $envelope
     *
     * @throws UnreadableResponse
     */
    public static function of(string $job, int $status, Envelope $envelope): self
    {
        if ($envelope->kind === Kind::Ended->value) {
            return new self($job, null, self::endingIn($envelope->data));
        }

        if ($envelope->kind !== Kind::Job->value) {
            return new self($job, $envelope, null);
        }

        if ($status !== self::STILL_GOING) {
            throw UnreadableResponse::namedAt($status);
        }

        return new self($job, null, null);
    }

    /**
     * Say what happens for each of the three, and answer with whichever fits.
     *
     * Every arm is required, so a state cannot be forgotten into a fall-through.
     * Give them by name.
     *
     * @template T
     *
     * @param  callable(): T  $stillRunning
     * @param  callable(Envelope<mixed>): T  $finished
     * @param  callable(JobEnding): T  $ended
     * @return T
     */
    public function answering(callable $stillRunning, callable $finished, callable $ended): mixed
    {
        if ($this->outcome instanceof Envelope) {
            return $finished($this->outcome);
        }

        return $this->ending === null ? $stillRunning() : $ended($this->ending);
    }

    /**
     * Why the work ended, read from the `ended` envelope's payload.
     *
     * @return JobEnding
     *
     * @throws UnreadableResponse
     */
    private static function endingIn(mixed $data): string
    {
        $ending = is_array($data) ? $data['ended'] ?? null : null;

        if (! in_array($ending, self::ENDINGS, true)) {
            throw UnreadableResponse::endingUnknown();
        }

        return $ending;
    }
}
