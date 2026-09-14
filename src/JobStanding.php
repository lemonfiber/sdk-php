<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Generated\Kind;

/**
 * Where the work one name stands for got to.
 *
 * An action that reaches the services runs for minutes, so it is not waited
 * for: the request is answered with a name, and the name is redeemed
 * afterwards for whatever became of the work. This is what redeeming one says.
 *
 * **Three states across two statuses, and neither half tells them apart on its
 * own.** Still going and ended are both the `job` envelope; ended and finished
 * are both `200`. Only the pair separates them, which is why the reading is
 * here rather than at each call site:
 *
 * | status | kind | where it got to |
 * |---|---|---|
 * | `202` | `job` | still going |
 * | `200` | the command's own | finished, and this is what it came to |
 * | `200` | `job` | ended before it finished |
 *
 * **The third is the one to get right.** Work that ended was released by name
 * or let go for want of anybody asking, and it is neither of the other two: a
 * client that read it as still going polls a name nothing is doing until the
 * stack is stopped, and one that read it as a stack it could not reach reports
 * a machine as broken when it answered perfectly and promptly. So there is no
 * way to read this that does not say what happens for it —
 * {@see self::answering()} takes an arm for each of the three and has no
 * default.
 *
 * A failure is not one of the three. Work that stopped on a problem answers
 * with the `error` envelope at the status that problem warrants, which is a
 * refusal and arrives as {@see Exception\RequestFailed} carrying the sentence
 * lemonfiber wrote; a name this run never handed out arrives as
 * {@see Exception\NoSuchJob}.
 *
 * The action the name was for is not carried here. It is on the `job` envelope
 * {@see Client::repair()} already answered with, which is where a caller holds
 * it — and it is absent from the finished answer, so a field for it would be
 * one that is there in two states and missing from the third.
 */
final readonly class JobStanding
{
    /**
     * The status work still going is answered under.
     */
    private const int STILL_GOING = 202;

    /**
     * @param Envelope<mixed>|null $outcome
     */
    private function __construct(
        public string $job,
        private ?Envelope $outcome,
        private bool $running,
    ) {}

    /**
     * The standing an answer describes, read from both halves of it.
     *
     * @param Envelope<mixed> $envelope
     */
    public static function of(string $job, int $status, Envelope $envelope): self
    {
        if ($envelope->kind !== Kind::Job->value) {
            return new self($job, $envelope, false);
        }

        return new self($job, null, $status === self::STILL_GOING);
    }

    /**
     * Say what happens for each of the three, and answer with whichever fits.
     *
     * Every arm is required, so a state cannot be forgotten into a fall-through.
     * Give them by name — the two that take nothing are alike enough that
     * position is a poor way to tell them apart.
     *
     * @template T
     *
     * @param  callable(): T  $stillRunning
     * @param  callable(Envelope<mixed>): T  $finished
     * @param  callable(): T  $ended
     * @return T
     */
    public function answering(callable $stillRunning, callable $finished, callable $ended): mixed
    {
        if ($this->running) {
            return $stillRunning();
        }

        return $this->outcome instanceof Envelope ? $finished($this->outcome) : $ended();
    }
}
