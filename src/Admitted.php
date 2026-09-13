<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk;

use Lemonfiber\Sdk\Exception\UnreadableResponse;

/**
 * What the door hands back: a session, and when it stops being one.
 *
 * The token travels in the same header the per-run token does, so a caller
 * holds one thing rather than two — which is the surface's own decision and the
 * reason {@see Client::at()} takes a token rather than a kind of token.
 *
 * **`until` is carried because a client that ignores it re-learns it the hard
 * way.** A session that has expired answers like one that was never valid, and
 * the difference matters to whoever is looking at the screen: an expired
 * session is answered by signing in again, and a rejected one may not be.
 *
 * **Carried as a count of seconds rather than as the text that arrived.** The
 * wire format is this client's business and nobody else's — a caller handed the
 * stamp would have to know how this product writes one, which is the knowledge
 * an SDK exists to hold. It is also the difference between one parse here and
 * one parse in every application that ever talks to a stack.
 */
final readonly class Admitted
{
    private function __construct(public string $token, public int $untilEpochSeconds) {}

    /**
     * The one place an answer becomes an admission.
     *
     * Takes the two strings the `admission` envelope carries rather than the
     * envelope, so that reading the envelope stays in one place —
     * {@see Admission} — and this type is something a test can build.
     *
     * **An unreadable ending refuses the whole answer** rather than becoming a
     * session with a guessed one. Either direction of guess is worse than the
     * refusal: a moment already past throws away a session the stack just
     * opened, and a far-future one leaves an application believing in a session
     * long after the stack has stopped honouring it. Neither is something a
     * caller could find out about, and this is.
     *
     * @throws UnreadableResponse
     */
    public static function of(string $token, string $until): self
    {
        $seconds = Stamp::secondsIn($until);

        if ($seconds === null) {
            throw UnreadableResponse::endingUnreadable($until);
        }

        return new self($token, $seconds);
    }
}
