<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk;

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
 * session is answered by signing in again, and a rejected one may not be. The
 * value is the server's word for when, as it sent it.
 */
final readonly class Admitted
{
    private function __construct(public string $token, public string $until) {}

    /**
     * The one place an answer becomes an admission.
     *
     * Takes the two strings the `admission` envelope carries rather than the
     * envelope, so that reading the envelope stays in one place —
     * {@see Admission} — and this type is something a test can build.
     */
    public static function of(string $token, string $until): self
    {
        return new self($token, $until);
    }
}
