<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Contract;

use Lemonfiber\Sdk\Generated\Contract;

/**
 * The wire contract this client speaks.
 */
final class Api
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
     * The endpoint answering with what the services have been saying.
     *
     * Named here for the reason {@see self::EVENTS_ENDPOINT} is: the contract
     * carries envelope kinds and no endpoints, so a path is knowledge this
     * client holds on its callers' behalf, and a caller spelling one is a
     * caller that breaks silently the day lemonfiber moves it.
     *
     * The one read whose answer is not a single envelope. It reaches no
     * command and renders no report: it opens the scrollback and writes a `log`
     * envelope per line, one document a line, which is a body read by
     * `EnvelopeReader::readEach()` rather than by `EnvelopeReader::read()`.
     * `Logs` is what asks for it, and asks for a look that ends.
     */
    public const string LOGS_ENDPOINT = '/api/logs';

    /**
     * The endpoint answering with a diagnosis.
     *
     * Named here rather than by whoever asks, which is the same argument
     * {@see self::EVENTS_ENDPOINT} makes: the contract carries envelope kinds
     * and no endpoints, so a path is knowledge this client holds on its
     * callers' behalf, and a caller spelling one is a caller that breaks
     * silently the day lemonfiber moves it.
     *
     * It runs `doctor` and answers with the `doctor` envelope, so
     * {@see \Lemonfiber\Sdk\Generated\DoctorEnvelope} is what reads it. A
     * read, so it neither accepts a warning nor opts into the checks that
     * disturb a running system — both of those change something and are
     * actions.
     */
    public const string CHECKS_ENDPOINT = '/api/checks';

    /**
     * The endpoint answering with what the whole stack is doing.
     *
     * Named here for the reason {@see self::CHECKS_ENDPOINT} is, as are the
     * reads beside it.
     *
     * It answers with the `status` envelope, so
     * {@see \Lemonfiber\Sdk\Generated\StatusEnvelope} is what reads it. It
     * takes nothing at all: what is running is a property of the machine, and
     * {@see self::SERVICES_ENDPOINT} is this same reading narrowed.
     */
    public const string STATUS_ENDPOINT = '/api/status';

    /**
     * The endpoint answering with what each service is doing.
     *
     * The reading {@see self::STATUS_ENDPOINT} takes whole, narrowed to the
     * forms that were named, under the one `status` envelope both arrive in.
     *
     * It takes `form`, and lemonfiber takes that one more than once — a
     * narrowing is a list of forms rather than a single one. Naming none is
     * the whole stack, which is what {@see self::STATUS_ENDPOINT} asks for
     * with nothing to name.
     */
    public const string SERVICES_ENDPOINT = '/api/services';

    /**
     * The endpoint answering with what the checks about the disk found.
     *
     * {@see self::CHECKS_ENDPOINT} held to the storage group, so it answers
     * with the same `doctor` envelope and
     * {@see \Lemonfiber\Sdk\Generated\DoctorEnvelope} is what reads it. The
     * narrowing is the path's own and not a caller's: it takes no parameter,
     * so this door cannot be turned into a second way of asking for any other
     * group.
     */
    public const string STORAGE_ENDPOINT = '/api/storage';

    /**
     * The endpoint answering with who is in the household, what each may
     * watch, and what each has asked for.
     *
     * It answers with the `household` envelope, so
     * {@see \Lemonfiber\Sdk\Generated\HouseholdEnvelope} is what reads it.
     *
     * It takes `member`, once, to narrow to one of them. Naming none is the
     * whole household and naming an empty one is refused, so a caller holding
     * a name it has not got must leave the parameter off rather than send it
     * blank.
     */
    public const string REQUESTS_ENDPOINT = '/api/requests';

    /**
     * The endpoint answering with what one member can actually watch.
     *
     * The sibling of {@see self::REQUESTS_ENDPOINT} and a different question of
     * the same household: that one says what has been *asked for*, and this says
     * what is already here.
     *
     * It answers with the `held` envelope, so
     * {@see \Lemonfiber\Sdk\Generated\HeldEnvelope} is what reads it.
     *
     * It takes `member`, naming whose shelf, and `most`, how many to answer with.
     * There is no whole-household form: the shelf is read from the media server
     * *as* that account, so the age limit, the blocked kinds and the libraries it
     * reaches are applied before the answer is written — and a single answer about
     * a house would be wrong for whoever it was not read as.
     */
    public const string HELD_ENDPOINT = '/api/held';

    /**
     * The endpoint answering with where a copy stands and what moving it comes to.
     *
     * Named here for the reason {@see self::CHECKS_ENDPOINT} is, as are the
     * reads beside it. A read and never a replacement: what it answers with
     * about this program is the exact command for whichever tool owns the copy
     * that is running, which is a thing to put in front of somebody rather
     * than a thing this surface carries out.
     *
     * **It takes `what`, and naming nothing is refused.** Two things can be
     * moved forward and the endpoint serves both, so a request that does not
     * say which is answered in prose rather than with an envelope. `stack`
     * asks where the services stand, which arrives as the `update` envelope
     * and {@see \Lemonfiber\Sdk\Generated\UpdateEnvelope} is what reads it.
     * `self` asks where this copy of lemonfiber stands, which is the
     * `self-update` envelope and a different type
     * ({@see \Lemonfiber\Sdk\Generated\SelfUpdateEnvelope}).
     *
     * It also takes `to`, the version to move to instead of whatever is
     * newest, which is the one question a downgrade asks. Only the `self`
     * reading reads it; named beside `stack` it is dropped rather than
     * refused, so a caller asking about the services names neither.
     */
    public const string UPDATE_ENDPOINT = '/api/update';

    /**
     * The endpoint answering with the items whose downloads have stopped.
     *
     * It answers with the `stuck` envelope, so
     * {@see \Lemonfiber\Sdk\Generated\StuckEnvelope} is what reads it, and it
     * takes nothing.
     */
    public const string STUCK_ENDPOINT = '/api/stuck';

    /**
     * The endpoint every action is asked for through.
     *
     * One path for the whole of what this surface can be told to do: the name
     * of the action is the last segment of it, and no action has an endpoint
     * of its own. Named here for the reason {@see self::CHECKS_ENDPOINT} is,
     * and {@see self::action()} beside it is what keeps a caller from spelling
     * either half.
     */
    public const string ACTIONS_ENDPOINT = '/api/actions';

    /**
     * The endpoint work already begun is asked about through.
     *
     * The name of the work is the last segment, as an action's name is the last
     * segment of {@see self::ACTIONS_ENDPOINT} — and for the same reason it is
     * named here. A name lemonfiber answered with is only an answer if it can
     * be redeemed, and a caller that had to spell where is a caller holding a
     * word with nothing to do with it.
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
     * Composed from the name rather than written out per action, so there is
     * one place the path is spelled and one place it moves. What names are
     * offered is the surface's own list and not this client's to hold: a name
     * lemonfiber does not offer is refused by name, which is an answer a
     * caller can act on, and a list kept here would go stale silently instead.
     */
    public static function action(string $name): string
    {
        return self::ACTIONS_ENDPOINT . '/' . $name;
    }

    /**
     * Where the work of that name is asked about, and released.
     *
     * One path for both, since asking what became of a name and letting it go
     * are one question and one answer: releasing ends the work and reports
     * where it now stands, which is what asking would have said. The method
     * separates them.
     */
    public static function job(string $name): string
    {
        return self::JOBS_ENDPOINT . '/' . $name;
    }
}
