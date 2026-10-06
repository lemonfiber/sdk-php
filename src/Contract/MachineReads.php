<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Contract;

/**
 * The reads about the machine itself: what it runs and how it stands, what it is set to, what
 * it keeps and holds, and what it can do.
 *
 * Held on an interface {@see Api} implements, so every one is `Api::…_ENDPOINT` to a caller,
 * as every path this client holds is. The reads about the household and who gets in are
 * {@see HouseholdReads}.
 */
interface MachineReads
{
    /**
     * The endpoint answering with what the services have been saying.
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
     * it takes nothing. What this client speaks is {@see Api::VERSION}, which
     * is a different question: that one is the wire version these types were
     * generated from, and this is what the machine is running.
     */
    public const string VERSION_ENDPOINT = '/api/version';

    /**
     * The endpoint answering with the forms this stack offers, or with what
     * starting some of them would come to.
     *
     * It takes `form`, more than once. Forms named are answered with the
     * `preview` envelope, {@see \Lemonfiber\Sdk\Generated\PreviewEnvelope}:
     * what starting them would bring up and leave out, and nothing started.
     * A request naming none is answered with the `forms` envelope, which
     * {@see \Lemonfiber\Sdk\Generated\FormsEnvelope} reads. A form is what
     * {@see Api::SERVICES_ENDPOINT} narrows by, so this is where a caller
     * finds the names that reading will accept.
     */
    public const string FORMS_ENDPOINT = '/api/forms';

    /**
     * The endpoint answering with what the whole stack is doing.
     *
     * It answers with the `status` envelope, so
     * {@see \Lemonfiber\Sdk\Generated\StatusEnvelope} is what reads it. It
     * takes nothing at all: what is running is a property of the machine, and
     * {@see Api::SERVICES_ENDPOINT} is this same reading narrowed.
     */
    public const string STATUS_ENDPOINT = '/api/status';

    /**
     * The endpoint answering with what each service is doing.
     *
     * The reading {@see Api::STATUS_ENDPOINT} takes whole, narrowed to the
     * forms that were named, under the one `status` envelope both arrive in.
     *
     * It takes `form`, and lemonfiber takes that one more than once — a
     * narrowing is a list of forms rather than a single one. Naming none is
     * the whole stack, which is what {@see Api::STATUS_ENDPOINT} asks for
     * with nothing to name.
     */
    public const string SERVICES_ENDPOINT = '/api/services';

    /**
     * The endpoint answering with what the checks about the disk found.
     *
     * {@see Api::CHECKS_ENDPOINT} held to the storage group, so it answers with the same `doctor`
     * envelope and {@see \Lemonfiber\Sdk\Generated\DoctorEnvelope} is what reads it. The narrowing
     * is the path's own and not a caller's: it takes no parameter, so this door cannot be turned
     * into a second way of asking for any other group.
     */
    public const string STORAGE_ENDPOINT = '/api/storage';

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
     * {@see Api::REQUESTS_ENDPOINT} has, and refused in the same place, so a line
     * typed at a screen and a query string arriving empty are answered alike.
     *
     * **Reading is all this does.** Changing a setting is an action and goes through
     * {@see Api::action()}, which is what keeps a read that a browser may repeat
     * apart from a write it may not.
     */
    public const string CONFIG_ENDPOINT = '/api/config';

    /**
     * The endpoint answering with what quality was asked for.
     *
     * The other half of the choices in force, beside {@see Api::CONFIG_ENDPOINT}:
     * that one is what the settings say and this is which preset is standing.
     *
     * It answers with the `quality` envelope, so
     * {@see \Lemonfiber\Sdk\Generated\QualityEnvelope} is what reads it, and
     * it takes nothing — a preset is one choice for the machine rather than a
     * list to narrow.
     *
     * **Reading is all this does.** Choosing a preset is an action and goes
     * through {@see Api::action()}, the same line
     * {@see Api::CONFIG_ENDPOINT} draws.
     */
    public const string QUALITY_ENDPOINT = '/api/quality';

    /**
     * The endpoint answering with where a copy stands and what moving it comes to.
     *
     * A read and never a replacement: what it answers with about this program
     * is the exact command for whichever tool owns the copy that is running,
     * which is a thing to put in front of somebody rather than a thing this
     * surface carries out.
     *
     * **It takes `what`, and naming nothing is refused.** Two things can be moved forward and the
     * endpoint serves both, so a request that does not say which is answered in prose rather than
     * with an envelope. `stack` asks where the services stand, which arrives as the `update`
     * envelope and {@see \Lemonfiber\Sdk\Generated\UpdateEnvelope} is what reads it. `self` asks
     * where this copy of lemonfiber stands, which is the `self-update` envelope and a different
     * type ({@see \Lemonfiber\Sdk\Generated\SelfUpdateEnvelope}).
     *
     * It also takes `to`, the version to move to instead of whatever is
     * newest, which is the one question a downgrade asks. Only the `self`
     * reading reads it; named beside `stack` it is dropped rather than
     * refused, so a caller asking about the services names neither.
     */
    public const string UPDATE_ENDPOINT = '/api/update';

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
     * description {@see Api::CATALOGUE_ENDPOINT} is. It answers with the
     * `provenance` envelope ({@see \Lemonfiber\Sdk\Generated\ProvenanceEnvelope}) and takes nothing.
     */
    public const string PROVENANCE_ENDPOINT = '/api/provenance';

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
     * What a surface can mark as new: releases, requests and checks found wrong, newest first.
     * It answers with the `news-items` envelope
     * ({@see \Lemonfiber\Sdk\Generated\NewsItemsEnvelope}) and takes nothing; the stream
     * names the newest of each as `news`.
     */
    public const string NEWS_ENDPOINT = '/api/news';

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
     * own. It answers with the `archives` envelope
     * ({@see \Lemonfiber\Sdk\Generated\ArchivesEnvelope}) and takes nothing.
     */
    public const string BACKUPS_ENDPOINT = '/api/backups';

    /**
     * The endpoint answering with the support bundle that was asked for.
     *
     * The bundle is handed over whole, as a file rather than a value, so no
     * envelope arrives here: the body is the bundle file itself. The name of
     * the bundle is the last segment, as an action's name is the last segment
     * of {@see Api::ACTIONS_ENDPOINT}, and {@see Api::bundle()} is what
     * keeps a caller from spelling either half. It takes no parameter at all —
     * the name it needs is in the path.
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
     * The endpoint answering with the plugins this machine has installed, as
     * the `plugins` envelope ({@see \Lemonfiber\Sdk\Generated\PluginsEnvelope}).
     * It takes nothing, and a record that cannot be read is refused with a code
     * of its own rather than answered as an empty list.
     */
    public const string PLUGINS_ENDPOINT = '/api/plugins';

    /**
     * The endpoint answering with what the stack wires to what, as the `wiring`
     * envelope ({@see \Lemonfiber\Sdk\Generated\WiringEnvelope}). It takes
     * nothing, and a wiring that cannot be read is refused with a code of its
     * own rather than answered as an empty one.
     */
    public const string WIRING_ENDPOINT = '/api/wiring';

    /**
     * The endpoint answering with what this stack can do, as the `capabilities` envelope
     * ({@see \Lemonfiber\Sdk\Generated\CapabilitiesEnvelope}).
     *
     * Each path the surface serves is mapped to available, unconfigured or not permitted,
     * and a path the stack does not have is absent rather than answered as false. It is
     * scoped to the credential that asked, so a household member's set carries what the
     * core already says they may do. It takes nothing, and a caller reads it rather than
     * deriving it from a version.
     */
    public const string CAPABILITIES_ENDPOINT = '/api/capabilities';

    /**
     * The endpoint answering with where the first-run walk stands, as the `setup` envelope
     * ({@see \Lemonfiber\Sdk\Generated\SetupEnvelope}).
     *
     * The one read of the walk: answering it, moving through it and applying it are
     * writes. It takes nothing.
     */
    public const string SETUP_ENDPOINT = '/api/setup';
}
