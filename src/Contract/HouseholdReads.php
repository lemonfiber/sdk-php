<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Contract;

/**
 * The reads about the household and who gets in: who is in it, what each has asked for and
 * can watch, where a request got to, and the doors, keys and credentials that let them in.
 *
 * Held on an interface {@see Api} implements, so every one is `Api::…_ENDPOINT` to a caller,
 * as every path this client holds is. The reads about the machine itself are
 * {@see MachineReads}.
 */
interface HouseholdReads
{
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
     * The sibling of {@see Api::REQUESTS_ENDPOINT} and a different question of
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
     *
     * One title on that shelf is the id the shelf lists it under, as the last
     * segment, and {@see Api::title()} is what composes it. That answers with the
     * `title` envelope ({@see \Lemonfiber\Sdk\Generated\TitleEnvelope}): what
     * it is, a series' seasons and episodes, and where each is served at the
     * guarded front door. It takes `member` and `defaults`, and a title outside
     * the member's limits is answered as absent, as one the household does not
     * hold is.
     */
    public const string HELD_ENDPOINT = '/api/held';

    /**
     * The endpoint answering with what one member was part-way through and how
     * far, most recent first, each located at the guarded front door as a title
     * read through {@see Api::title()} is.
     *
     * It answers with the `part-way` envelope, so
     * {@see \Lemonfiber\Sdk\Generated\PartWayEnvelope} is what reads it.
     *
     * It takes `member`, naming whose, and `most`, how many to answer with. Like
     * the shelf it is read as that member, so there is no whole-household form.
     */
    public const string WATCHING_ENDPOINT = '/api/watching';

    /**
     * The endpoint answering with what the media server is playing now: who is
     * watching what, on which device, and whether it is paused.
     *
     * It answers with the `playing` envelope, so
     * {@see \Lemonfiber\Sdk\Generated\PlayingEnvelope} is what reads it.
     *
     * It takes `member`, once, by name or id, to narrow to one of them; naming
     * none is every session in the house. A member, or a key scoped to one, is
     * answered with their own sessions whatever the request named.
     */
    public const string PLAYING_ENDPOINT = '/api/playing';

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
     * {@see Api::REQUESTS_ENDPOINT} and {@see Api::STUCK_ENDPOINT} either
     * side: the household's requests are the list, this follows one of them,
     * and the stuck reading is the same question from the other end — which is
     * why each entry a stuck reading names is named the way this is asked for.
     *
     * It answers with the `trace` envelope ({@see \Lemonfiber\Sdk\Generated\TraceEnvelope}) and takes
     * `term`, what is being followed, and `season`, narrowing a television one.
     */
    public const string TRACE_ENDPOINT = '/api/trace';

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
     * surface, not here and not through {@see Api::action()}. A value sent
     * through this door would pass a browser's cache, whatever proxy is
     * between and the log each of them keeps; both belong at the terminal, in
     * front of the person who typed the confirmation.
     */
    public const string CREDENTIALS_ENDPOINT = '/api/credentials';

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
     * The endpoint answering with the integration keys the credential that asked may see,
     * without their secrets, as the `keys` envelope
     * ({@see \Lemonfiber\Sdk\Generated\KeysEnvelope}).
     *
     * An operator session lists every key, and a household member's lists only the keys
     * scoped to them; a key is refused here whatever its scope. It takes nothing, and
     * minting and revoking are writes.
     */
    public const string KEYS_ENDPOINT = '/api/keys';
}
