<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Http;

use Lemonfiber\Sdk\Time\Clock;
use Lemonfiber\Sdk\Time\Deadline;
use Lemonfiber\Sdk\Time\Duration;
use LogicException;
use Saloon\Http\PendingRequest;
use Saloon\Http\Request;

/**
 * One call: every attempt at one request, and the wait they share.
 *
 * Made fresh for each request the connector sends and carried on that request,
 * so nothing about a call outlives it or is shared with the next one. Each
 * attempt is given what is left of the wait, and an attempt the pause before it
 * would not leave room for is not made.
 */
final class Call
{
    /** Where a request carries its call. */
    private const string KEY = 'lemonfiber-call';

    private const int MILLISECONDS_PER_SECOND = 1000;

    /** How many times this call has been asked again. */
    private int $askedAgain = 0;

    private function __construct(private readonly Deadline $deadline) {}

    /**
     * A call that may take this long from now, carried on the request it is a call to.
     */
    public static function on(Request $request, Duration $wait, Clock $clock): self
    {
        $call = new self(Deadline::after($wait, $clock));
        $request->config()->add(self::KEY, $call);

        return $call;
    }

    /**
     * The call a request about to be sent belongs to.
     *
     * @throws LogicException where the request was sent some way other than
     *                        through the connector, and so belongs to no call
     */
    public static function of(PendingRequest $pendingRequest): self
    {
        $call = $pendingRequest->config()->get(self::KEY);

        if (! $call instanceof self) {
            throw new LogicException('A request reached the transport without the call it belongs to; send it through LemonfiberConnector.');
        }

        return $call;
    }

    /**
     * Seconds left before the wait is over, and none once it is.
     */
    public function secondsLeft(): float
    {
        return $this->deadline->secondsLeft();
    }

    /**
     * Whether the pause before asking again still leaves room within the wait.
     *
     * The pause doubles with each time this call is asked again, as the
     * requests that ask again are told to.
     */
    public function hasRoomToAskAgain(int $firstPauseMs): bool
    {
        $pause = $firstPauseMs * (2 ** $this->askedAgain) / self::MILLISECONDS_PER_SECOND;
        $this->askedAgain++;

        return $this->deadline->hasRoomFor($pause);
    }
}
