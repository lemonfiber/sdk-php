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
 * spelling one breaks silently the day lemonfiber moves it.
 * `scripts/the_doors_this_client_names.py` holds this list to the contract page
 * in both directions, a read named there being unreachable without one.
 *
 * **Reading is all of them do.** Where a read has a change beside it — choosing
 * an alert preset, declaring a bandwidth limit, agreeing to what the disk
 * account offered, putting a journalled change back, installing a hosted
 * command, acting on what a migration survey found — the change is an action
 * and goes through {@see self::action()}. That is the line between what a
 * browser may repeat and what it may not.
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
     * The endpoint answering with the versions in play.
     *
     * It answers with the `version` envelope, so
     * {@see \Lemonfiber\Sdk\Generated\VersionEnvelope} is what reads it, and
     * it takes nothing. What this client speaks is {@see self::VERSION}, which
     * is a different question: that one is the wire version these types were
     * generated from, and this is what the machine is running.
     */
    public const string VERSION_ENDPOINT = '/api/version';

    /**
     * The endpoint answering with the forms this stack offers.
     *
     * It answers with the `forms` envelope, so
     * {@see \Lemonfiber\Sdk\Generated\FormsEnvelope} is what reads it, and it
     * takes nothing. A form is what {@see self::SERVICES_ENDPOINT} narrows by,
     * so this is where a caller finds the names that reading will accept.
     */
    public const string FORMS_ENDPOINT = '/api/forms';

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
     * The endpoint answering with what the stack is configured to do.
     *
     * It answers with the `config` envelope, so
     * {@see \Lemonfiber\Sdk\Generated\ConfigEnvelope} is what reads it, and what
     * it carries is the settings the stack holds rather than a list this client
     * knows: a consumer enumerating the settings it has heard of offers a subset the
     * day the stack grows one, and offers it silently.
     *
     * It takes `key`, once, to narrow to one of them. Naming none is every setting
     * and naming an empty one is refused, so a caller holding a key it has not got
     * must leave the parameter off rather than send it blank — the same shape
     * {@see self::REQUESTS_ENDPOINT} has, and refused in the same place, so a line
     * typed at a screen and a query string arriving empty are answered alike.
     *
     * **Reading is all this does.** Changing a setting is an action and goes through
     * {@see self::action()}, which is what keeps a read that a browser may repeat
     * apart from a write it may not.
     */
    public const string CONFIG_ENDPOINT = '/api/config';

    /**
     * The endpoint answering with what quality was asked for.
     *
     * The other half of the choices in force, beside {@see self::CONFIG_ENDPOINT}:
     * that one is what the settings say and this is which preset is standing.
     *
     * It answers with the `quality` envelope, so
     * {@see \Lemonfiber\Sdk\Generated\QualityEnvelope} is what reads it, and
     * it takes nothing — a preset is one choice for the machine rather than a
     * list to narrow.
     *
     * **Reading is all this does.** Choosing a preset is an action and goes
     * through {@see self::action()}, the same line
     * {@see self::CONFIG_ENDPOINT} draws.
     */
    public const string QUALITY_ENDPOINT = '/api/quality';

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
     * The endpoint answering with where one item got to.
     *
     * The middle of three readings of one subject, with
     * {@see self::REQUESTS_ENDPOINT} and {@see self::STUCK_ENDPOINT} either
     * side: the household's requests are the list, this follows one of them,
     * and the stuck reading is the same question from the other end — which is
     * why each entry a stuck reading names is named the way this is asked for.
     *
     * It answers with the `trace` envelope ({@see \Lemonfiber\Sdk\Generated\TraceEnvelope}) and takes
     * `term`, what is being followed, and `season`, narrowing a television one.
     */
    public const string TRACE_ENDPOINT = '/api/trace';

    /**
     * The endpoint answering with what the operator is told about.
     *
     * The reading half of the word and only that half: the preset in force,
     * what it means in the operator's own terms, and any event kind set apart
     * from it. It answers with the `alerts` envelope
     * ({@see \Lemonfiber\Sdk\Generated\AlertsEnvelope}) and takes nothing.
     */
    public const string ALERTS_ENDPOINT = '/api/alerts';

    /**
     * The endpoint answering with how the line is shared.
     *
     * What the line was measured to carry, what the stack is held to, which
     * side of the household's day each download client says it is on, and
     * whether each is keeping to what it was given. It answers with the
     * `bandwidth` envelope ({@see \Lemonfiber\Sdk\Generated\BandwidthEnvelope}) and takes nothing.
     */
    public const string BANDWIDTH_ENDPOINT = '/api/bandwidth';

    /**
     * The endpoint answering with where the disk went.
     *
     * The account of the disk and an offer of what could be got back. It
     * answers with the `space` envelope ({@see \Lemonfiber\Sdk\Generated\SpaceEnvelope}) and takes
     * nothing: there is no parameter choosing what to reclaim, and that choice
     * is never a caller's to make.
     */
    public const string SPACE_ENDPOINT = '/api/space';

    /**
     * The endpoint answering with what this machine keeps of lemonfiber's.
     *
     * Where lemonfiber's own files are on the host, what each is, and which
     * hold a credential — the reading a caller with no filesystem in front of
     * it cannot answer at all. It answers with the `stored` envelope
     * ({@see \Lemonfiber\Sdk\Generated\StoredEnvelope}) and takes nothing.
     */
    public const string STORED_ENDPOINT = '/api/stored';

    /**
     * The endpoint answering with everything that leaves this machine.
     *
     * One list, over the settings this machine actually holds. What a caller
     * can see of its own traffic is what it asked for and nothing of what the
     * process behind it does. It answers with the `outbound` envelope
     * ({@see \Lemonfiber\Sdk\Generated\OutboundEnvelope}) and takes nothing.
     */
    public const string OUTBOUND_ENDPOINT = '/api/outbound';

    /**
     * The endpoint answering with what each service is for.
     *
     * Read out of the stack description this machine runs, so a caller holding
     * its own copy would describe whichever stack its author had in mind. It
     * answers with the `catalogue` envelope
     * ({@see \Lemonfiber\Sdk\Generated\CatalogueEnvelope}) and takes nothing.
     */
    public const string CATALOGUE_ENDPOINT = '/api/catalogue';

    /**
     * The endpoint answering with where the services come from.
     *
     * The licences and origins of what is bundled, read out of the same stack
     * description {@see self::CATALOGUE_ENDPOINT} is. It answers with the
     * `provenance` envelope ({@see \Lemonfiber\Sdk\Generated\ProvenanceEnvelope}) and takes nothing.
     */
    public const string PROVENANCE_ENDPOINT = '/api/provenance';

    /**
     * The endpoint answering with which app to watch on.
     *
     * It answers with the `clients` envelope
     * ({@see \Lemonfiber\Sdk\Generated\ClientsEnvelope}) and takes nothing: what to watch on is the
     * same answer on every machine, so this needs neither a stack running nor
     * a daemon reachable — which is when somebody deciding what to tell the
     * house is most likely to ask.
     */
    public const string CLIENTS_ENDPOINT = '/api/clients';

    /**
     * The endpoint answering with the credentials this stack holds.
     *
     * What each is, what authenticates with it, where its value lives and
     * where it stands — and **no value at all**, the shape it is built from
     * having nowhere to put one. It answers with the `credentials` envelope
     * ({@see \Lemonfiber\Sdk\Generated\CredentialsEnvelope}) and takes nothing.
     *
     * Printing a credential and replacing one are offered nowhere on this
     * surface, not here and not through {@see self::action()}. A value sent
     * through this door would pass a browser's cache, whatever proxy is
     * between and the log each of them keeps; both belong at the terminal, in
     * front of the person who typed the confirmation.
     */
    public const string CREDENTIALS_ENDPOINT = '/api/credentials';

    /**
     * The endpoint answering with what lemonfiber has already changed.
     *
     * Every journalled change newest first, what each did in the operator's
     * own terms, and how far each could be put back — whole, in part, or not
     * at all, with the reason where it is not. It answers with the `history`
     * envelope ({@see \Lemonfiber\Sdk\Generated\HistoryEnvelope}) and takes nothing.
     */
    public const string HISTORY_ENDPOINT = '/api/history';

    /**
     * The endpoint answering with what this machine keeps running when nobody
     * is watching.
     *
     * Not that a definition was written, but that the machine confirms it is
     * keeping the command going. It answers with the `hosting` envelope
     * ({@see \Lemonfiber\Sdk\Generated\HostingEnvelope}) and takes nothing.
     */
    public const string HOSTING_ENDPOINT = '/api/hosting';

    /**
     * The endpoint answering with what is already on this machine.
     *
     * A survey and nothing else: the projects already standing here, the ports
     * they hold that lemonfiber would want, and what it could not take over.
     * It answers with the `migration` envelope
     * ({@see \Lemonfiber\Sdk\Generated\MigrationEnvelope}) and takes nothing.
     */
    public const string MIGRATION_ENDPOINT = '/api/migration';

    /**
     * The one address to hand somebody who lives here.
     *
     * Every other reading answers something an operator asks about their
     * stack; this answers what they send to somebody who does not operate it,
     * which is why the answer names one service and says why nothing else it
     * lists is that service. It answers with the `front-door` envelope
     * ({@see \Lemonfiber\Sdk\Generated\FrontDoorEnvelope}) and takes nothing.
     */
    public const string FRONT_DOOR_ENDPOINT = '/api/front-door';

    /**
     * The endpoint answering with what one of this product's words means.
     *
     * The one reading about lemonfiber rather than about a stack, answered
     * from a table compiled into the binary — so a caller meeting an
     * unfamiliar word in a report can ask what it means with nothing else up.
     *
     * It takes `word`, once. Naming one explains that one, which arrives as
     * the `word` envelope ({@see \Lemonfiber\Sdk\Generated\WordEnvelope}); naming none lists them
     * all, which is the `glossary` envelope and a different type
     * ({@see \Lemonfiber\Sdk\Generated\GlossaryEnvelope}). A word this product does not explain is
     * refused rather than answered with the listing, and naming an empty one
     * is naming a word it does not explain.
     */
    public const string EXPLAIN_ENDPOINT = '/api/explain';

    /**
     * The endpoint answering with which backups are here to restore from.
     *
     * The command line names a path, having one in front of whoever typed it;
     * a caller here has none, so what a path would have given it is given by
     * the server instead. **A name, never a path** — the listing is names and
     * a restore is asked for by name, each resolved beneath a directory of its
     * own. It answers with the `backup` envelope
     * ({@see \Lemonfiber\Sdk\Generated\BackupEnvelope}) and takes nothing.
     */
    public const string BACKUPS_ENDPOINT = '/api/backups';

    /**
     * The endpoint answering with the support bundle that was asked for.
     *
     * The other half of what {@see self::BACKUPS_ENDPOINT} answers. The name
     * of the bundle is the last segment, as an action's name is the last
     * segment of {@see self::ACTIONS_ENDPOINT}, and {@see self::bundle()}
     * is what keeps a caller from spelling either half. It answers with the
     * `bundle` envelope ({@see \Lemonfiber\Sdk\Generated\BundleEnvelope}) and takes no parameter at
     * all — the name it needs is in the path.
     */
    public const string BUNDLE_ENDPOINT = '/api/bundle';

    /**
     * The endpoint answering with what taking lemonfiber off this machine
     * comes to.
     *
     * **A read and never a removal.** Every container, image and path a
     * removal would take, with what each occupies; the action of the same name
     * is where an answer to that listing goes, so the thing agreed to on one
     * surface is the thing read on the other. It answers with the `uninstall`
     * envelope ({@see \Lemonfiber\Sdk\Generated\UninstallEnvelope}).
     *
     * It takes `tier`, once, naming which of the four removals is being read.
     * Naming none reads the one that removes nothing, which is the safe
     * reading and the one a caller has chosen nothing by; a word naming none
     * of the four is refused rather than read as whichever the shape would
     * default to, since on this subject the default that would hurt is the one
     * that reaches the library.
     */
    public const string UNINSTALL_ENDPOINT = '/api/uninstall';

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
