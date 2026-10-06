<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Contract;

use Lemonfiber\Sdk\Generated\Contract;

/**
 * The wire contract this client speaks.
 *
 * Every read lemonfiber serves has a path here, held as a constant rather than
 * spelled by a caller: the contract carries envelope kinds and no endpoints, so
 * a path is knowledge this client keeps on its callers' behalf, and a caller
 * spelling one breaks silently the day lemonfiber moves it. The reads are
 * written on {@see MachineReads} and {@see HouseholdReads}, which this class
 * implements, so each is `Api::…_ENDPOINT` all the same.
 * `scripts/the_doors_this_client_names.py` holds them to the contract page in
 * both directions, a read named there being unreachable without one.
 *
 * **Reading is all of them do.** Where a read has a change beside it — choosing
 * an alert preset, declaring a bandwidth limit, agreeing to what the disk
 * account offered, putting a journalled change back, installing a hosted
 * command, acting on what a migration survey found — the change is an action
 * and goes through {@see self::action()}. That is the line between what a
 * browser may repeat and what it may not.
 */
final class Api implements HouseholdReads, MachineReads
{
    /**
     * The `api_version` integer this client speaks.
     *
     * Taken from the contract the types were generated from, so the wire
     * version is stated once.
     */
    public const int VERSION = Contract::API_VERSION;

    /**
     * The header every request carries the per-run token in.
     */
    public const string TOKEN_HEADER = 'X-Lemonfiber-Token';

    /**
     * The header a resumed stream names its last seen event in.
     */
    public const string RESUME_HEADER = 'Last-Event-ID';

    /**
     * The header an action names the single attempt it is part of in.
     *
     * A re-send inside that attempt carries the same value, so an answer lost
     * on the way back is not a second change to the machine. A later attempt
     * carries a fresh one, and nothing the first attempt asked for is applied
     * under it.
     *
     * The name the HTTP working group's draft gives it, rather than an
     * `X-Lemonfiber-` name of the sort {@see self::TOKEN_HEADER} is. That
     * prefix belongs to what this surface invented, and a retry key is none of
     * its invention: anything sitting in front of a stack already reads this
     * spelling and would never see a private one.
     */
    public const string IDEMPOTENCY_HEADER = 'Idempotency-Key';

    /**
     * The endpoint serving live updates.
     */
    public const string EVENTS_ENDPOINT = '/api/events';

    /**
     * The endpoint every action is asked for through.
     *
     * One path for the whole of what this surface can be told to do: the name of the action is the
     * last segment of it, and no action has an endpoint of its own. {@see self::action()} beside it
     * is what keeps a caller from spelling either half.
     */
    public const string ACTIONS_ENDPOINT = '/api/actions';

    /**
     * The endpoint work already begun is asked about through.
     *
     * The name of the work is the last segment, as an action's name is the last segment of
     * {@see self::ACTIONS_ENDPOINT} — and for the same reason it is named here. A name lemonfiber
     * answered with is only an answer if it can be redeemed, and a caller that had to spell where
     * is a caller holding a word with nothing to do with it.
     */
    public const string JOBS_ENDPOINT = '/api/jobs';

    /**
     * The media type live updates arrive as.
     */
    public const string EVENT_STREAM_MEDIA_TYPE = 'text/event-stream';

    /**
     * The media type every other answer arrives as.
     */
    public const string JSON_MEDIA_TYPE = 'application/json';

    /**
     * Where the action of that name is asked for.
     *
     * Composed from the name rather than written out per action, so there is one place the path is
     * spelled and one place it moves. What names are offered is the surface's own list and not this
     * client's to hold: a name lemonfiber does not offer is refused by name, which is an answer a
     * caller can act on, and a list kept here would go stale silently instead.
     */
    public static function action(string $name): string
    {
        return self::ACTIONS_ENDPOINT . '/' . $name;
    }

    /**
     * Where the work of that name is asked about, and released.
     *
     * One path for both, since asking what became of a name and letting it go are one question and
     * one answer: releasing ends the work and reports where it now stands, which is what asking
     * would have said. The method separates them.
     */
    public static function job(string $name): string
    {
        return self::JOBS_ENDPOINT . '/' . $name;
    }

    /**
     * Where the support bundle of that name is asked for.
     *
     * Composed from the name for the reason {@see self::action()} is: one place
     * the path is spelled and one place it moves. A name the server answered
     * with is only an answer if it can be redeemed, and a caller that had to
     * spell where is a caller holding a word with nothing to do with it.
     */
    public static function bundle(string $name): string
    {
        return self::BUNDLE_ENDPOINT . '/' . $name;
    }
}
