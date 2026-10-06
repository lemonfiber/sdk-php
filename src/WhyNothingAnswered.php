<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk;

/**
 * Which way a request met nothing, as far as the connection could tell.
 *
 * Each calls for a different remedy. A name that turns into no address
 * points at the name or at what looks names up; a refusal is a machine that is
 * there with nothing listening; no way to the address is nothing there to
 * answer for it; a wait that ran out is a machine that may not be there at
 * all. What the connection could not place is said to be so,
 * rather than given the nearest of the three.
 */
enum WhyNothingAnswered: string
{
    /** The address's name turned into no address from here. */
    case NameNotFound = 'name_not_found';

    /** Something at the address turned the connection away. */
    case Refused = 'refused';

    /**
     * No way to the address from here: nothing on the network answers for it,
     * which is what an address left behind by a machine that moved looks like.
     */
    case NoRoute = 'no_route';

    /** Nothing came back before the wait ran out. */
    case TimedOut = 'timed_out';

    /** The connection failed in a way none of the others names. */
    case Other = 'other';
}
