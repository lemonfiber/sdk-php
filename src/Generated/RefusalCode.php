<?php

// Generated from contract/web-api.contract.json. Do not edit.
// Source: 2112d04d879bc16f9a6f9f44aa4027613a48b803, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

/**
 * Every code the contract lists a refusal as carrying, and the status each is answered with.
 *
 * A code this list does not name is read by its status alone.
 */
enum RefusalCode: string
{
    /**
     * Raised when a request carried no token, session or key this run admits.
     *
     * Answered with 403.
     */
    case NotAdmitted = 'ADMIT-4';

    /**
     * Raised when a request said it came from somewhere this server is not.
     *
     * Answered with 403.
     */
    case Elsewhere = 'ADMIT-5';

    /**
     * Raised when an account asked for something that is not its to ask for.
     *
     * Answered with 403.
     */
    case NotYours = 'ADMIT-6';

    /**
     * Raised when the media server could not say whether an account is still one.
     *
     * Answered with 403.
     */
    case Unconfirmed = 'ADMIT-7';

    /**
     * Raised when the password offered at the door was wrong, or none is set.
     *
     * Answered with 401.
     */
    case NotThePassword = 'ADMIT-8';

    /**
     * Raised when the door has been given too many wrong passwords lately.
     *
     * Answered with 429.
     */
    case TooManyAttempts = 'ADMIT-9';

    /**
     * Raised when what was offered at the door is not a password.
     *
     * Answered with 400.
     */
    case NotAPassword = 'ADMIT-10';

    /**
     * Raised when a key arrived from another machine over a connection its pin does not verify.
     *
     * Answered with 403.
     */
    case KeyInTheClear = 'ADMIT-11';

    /**
     * Raised when a key asked for something its scope does not reach.
     *
     * Answered with 403.
     */
    case NotForAKey = 'ADMIT-12';

    /**
     * Raised where no action goes by the name that was asked for.
     *
     * Answered with 404.
     */
    case NoSuchAction = 'ASK-1';

    /**
     * Raised where an action was not given an argument it needs.
     *
     * Answered with 400.
     */
    case MissingArgument = 'ASK-2';

    /**
     * Raised where an argument was given a value that names nothing.
     *
     * Answered with 400.
     */
    case UnrecognisedArgument = 'ASK-3';

    /**
     * Raised where an action was given an argument its command has nowhere to put.
     *
     * Answered with 400.
     */
    case UnwantedArgument = 'ASK-4';

    /**
     * Raised where two arguments that each name a different request arrived together.
     *
     * Answered with 400.
     */
    case ArgumentsTogether = 'ASK-5';

    /**
     * Raised where the body of an action is not arguments it can read.
     *
     * Answered with 400.
     */
    case NotArguments = 'ASK-6';

    /**
     * Raised where a job was asked about that this run did not start.
     *
     * Answered with 404.
     */
    case NoSuchJob = 'ASK-7';

    /**
     * Raised where the body of a setup step is not an answer it can read.
     *
     * Answered with 400.
     */
    case NotAnAnswer = 'ASK-8';

    /**
     * Raised where a path under the endpoints is one no endpoint answers.
     *
     * Answered with 404.
     */
    case NoEndpoint = 'ASK-9';

    /**
     * Raised where an endpoint was asked with a method it does not answer.
     *
     * Answered with 405.
     */
    case WrongMethod = 'ASK-10';

    /**
     * Raised where the body of a mint is not a key's name, scope, purpose and the password.
     *
     * Answered with 400.
     */
    case NotAKeyRequest = 'ASK-11';

    /**
     * Raised when an agreement names a reading of this machine that is not the one standing now.
     *
     * Answered with 400.
     */
    case AnotherReading = 'GONE-2';

    /**
     * Raised when a replacement was agreed to for an offer that is not the one standing now.
     *
     * Answered with 400.
     */
    case OfferMoved = 'MIGRATE-1';

    /**
     * The source names no plugin this build can read.
     *
     * Answered with 404.
     */
    case Unreadable = 'PLUGIN-2';

    /**
     * The manifest is read and this build refuses what it declares.
     *
     * Answered with 400.
     */
    case Refused = 'PLUGIN-3';

    /**
     * The record of what is installed cannot be read.
     *
     * Answered with 500.
     */
    case Unrecorded = 'PLUGIN-4';

    /**
     * The plugin is installed already.
     *
     * Answered with 400.
     */
    case Already = 'PLUGIN-5';

    /**
     * There is no stack on this machine to put a plugin's container in.
     *
     * Answered with 500.
     */
    case Nowhere = 'PLUGIN-6';

    /**
     * A directory or a document the install decided on would not land.
     *
     * Answered with 500.
     */
    case Unwritable = 'PLUGIN-7';

    /**
     * The wiring went down and the record of what is installed did not.
     *
     * Answered with 500.
     */
    case Unrecordable = 'PLUGIN-8';

    /**
     * The plugin's own service would not start, so nothing about it could be proved.
     *
     * Answered with 500.
     */
    case Unproved = 'PLUGIN-9';

    /**
     * Nothing by that name is installed on this machine.
     *
     * Answered with 404.
     */
    case NothingToRemove = 'PLUGIN-10';

    /**
     * Nothing by that id is installed, so there is no version to replace.
     *
     * Answered with 404.
     */
    case NothingToUpdate = 'PLUGIN-11';

    /**
     * The version installed would not come off, so nothing else was touched.
     *
     * Answered with 500.
     */
    case Stuck = 'PLUGIN-12';

    /**
     * Raised when a plugin's service would answer on a label another plugin's already does.
     *
     * Answered with 400.
     */
    case Answered = 'PLUGIN-13';

    /**
     * Raised when a plugin is installed from a source other than the one its name is already installed from.
     *
     * Answered with 400.
     */
    case TwoSources = 'PLUGIN-14';

    /**
     * Raised when a plugin is named from a git source and fetching from one is switched off.
     *
     * Answered with 400.
     */
    case SourceOff = 'PLUGIN-15';

    /**
     * Raised when a git source could not be reached or would not hand over a revision.
     *
     * Answered with 500.
     */
    case Unfetched = 'PLUGIN-16';

    /**
     * Raised when a git source holds no branch, tag or commit by the name given.
     *
     * Answered with 404.
     */
    case NoRevision = 'PLUGIN-17';

    /**
     * Raised when a plugin is installed by name and asking the catalogue is switched off.
     *
     * Answered with 400.
     */
    case CatalogueOff = 'PLUGIN-18';

    /**
     * Raised when the catalogue's index or its signature could not be fetched.
     *
     * Answered with 500.
     */
    case CatalogueUnreachable = 'PLUGIN-19';

    /**
     * Raised when the catalogue's index has no signature, one that does not verify, or none this build carries a key to check.
     *
     * Answered with 500.
     */
    case SignatureUnverified = 'PLUGIN-20';

    /**
     * Raised when the catalogue's index verified and is not one this build reads.
     *
     * Answered with 500.
     */
    case CatalogueUnreadable = 'PLUGIN-21';

    /**
     * Raised when the catalogue holds no plugin by the name given.
     *
     * Answered with 404.
     */
    case NotCatalogued = 'PLUGIN-22';

    /**
     * Raised when what the catalogue's origin served is not what the catalogue reviewed.
     *
     * Answered with 500.
     */
    case NotAsReviewed = 'PLUGIN-23';

    /**
     * Raised when a plugin's service would be named, where lemonfiber keeps what a service holds, as another installed plugin's service already is.
     *
     * Answered with 400.
     */
    case SpelledAlike = 'PLUGIN-24';

    /**
     * Raised when an install, an update or a removal answers an offer that was read against a plugin, a stack or a record that has since moved.
     *
     * Answered with 400.
     */
    case PluginOfferMoved = 'PLUGIN-25';

    /**
     * Raised when a value a recipe would carry to a destination was not approved as itself, or an approval names a pair the recipe does not carry.
     *
     * Answered with 400.
     */
    case Unapproved = 'PLUGIN-26';

    /**
     * Raised when the source an update names holds a different plugin from the one it was asked to update.
     *
     * Answered with 400.
     */
    case AnotherPlugin = 'PLUGIN-27';

    /**
     * Raised when a plugin's service would take a name, a port or a label something already on this machine holds: a service of the stack or of the operator's overlay, another plugin's port, or a site in the proxy's live configuration.
     *
     * Answered with 400.
     */
    case Occupied = 'PLUGIN-28';

    /**
     * Raised when the catalogue's index verifies and is older than the newest one this machine has verified.
     *
     * Answered with 500.
     */
    case CatalogueReplaced = 'PLUGIN-29';

    /**
     * Raised when the record of the newest catalogue index this machine verified cannot be read or written.
     *
     * Answered with 500.
     */
    case NewestUnkept = 'PLUGIN-30';

    /**
     * Raised when a git source is named over a transport other than https, before anything is asked of it.
     *
     * Answered with 400.
     */
    case SchemeRefused = 'PLUGIN-31';

    /**
     * Raised when a git source's host is, or stands for, an address on this machine or on a network of its own: loopback, private, link-local or unspecified.
     *
     * Answered with 400.
     */
    case AddressRefused = 'PLUGIN-32';

    /**
     * Raised where a read was given a parameter its answer has nowhere to put.
     *
     * Answered with 400.
     */
    case Unwanted = 'READ-1';

    /**
     * Raised where a parameter carrying one value was given more than once.
     *
     * Answered with 400.
     */
    case Repeated = 'READ-2';

    /**
     * Raised where no read goes by the name that was asked for.
     *
     * Answered with 404.
     */
    case NoSuchRead = 'READ-3';

    /**
     * Raised where a trace was asked for and named nothing to follow.
     *
     * Answered with 400.
     */
    case NoTerm = 'READ-4';

    /**
     * Raised where the season to narrow a trace to is not a number.
     *
     * Answered with 400.
     */
    case NotASeason = 'READ-5';

    /**
     * Raised where a setting was asked for by an empty name.
     *
     * Answered with 400.
     */
    case NoSetting = 'READ-6';

    /**
     * Raised where a household member was asked for by an empty name.
     *
     * Answered with 400.
     */
    case NoMember = 'READ-7';

    /**
     * Raised where a shelf was asked for and nobody was named whose it is.
     *
     * Answered with 400.
     */
    case NoShelfWithoutAMember = 'READ-8';

    /**
     * Raised where how many holdings to answer with is not a whole number.
     *
     * Answered with 400.
     */
    case NotACount = 'READ-9';

    /**
     * Raised where more holdings were asked for than one read answers with.
     *
     * Answered with 400.
     */
    case TooManyAtOnce = 'READ-10';

    /**
     * Raised where a diagnosis was narrowed to a group or check that is not one.
     *
     * Answered with 400.
     */
    case NoSuchGroup = 'READ-11';

    /**
     * Raised where a removal was named that is none of the four there are.
     *
     * Answered with 400.
     */
    case NoSuchRemoval = 'READ-12';

    /**
     * Raised where moving forward was asked about and neither stack nor self named.
     *
     * Answered with 400.
     */
    case NoUpdateObject = 'READ-13';

    /**
     * Raised where how many log lines to begin with is not a number within the ceiling.
     *
     * Answered with 400.
     */
    case NotALineCount = 'READ-14';

    /**
     * Raised where a parameter that takes a yes or a no is neither true nor false.
     *
     * Answered with 400.
     */
    case NotAChoice = 'READ-15';

    /**
     * Raised where a household read named a member and asked for the household's defaults as well.
     *
     * Answered with 400.
     */
    case MemberAndDefaults = 'READ-16';

    /**
     * Raised when consent was given for an offer that no longer stands.
     *
     * Answered with 400.
     */
    case Stale = 'REPAIR-1';

    /**
     * Raised when consent was given for a listing that no longer stands.
     *
     * Answered with 400.
     */
    case MovedOn = 'RESTORE-11';

    /**
     * Raised when an answer could not be rendered.
     *
     * Answered with 500.
     */
    case Unrenderable = 'SERVE-6';

    /**
     * Raised when this machine will not supply the randomness a job is named with.
     *
     * Answered with 500.
     */
    case NoJobName = 'SERVE-7';

    /**
     * Raised when an agreement names an offer that is not the one standing now.
     *
     * Answered with 400.
     */
    case AnotherOffer = 'SPACE-6';

    /**
     * Raised when a stack directory holds no readable manifest.
     *
     * Answered with 500.
     */
    case StackUnreadable = 'STACK-1';

    /**
     * Raised when a manifest is readable and this build cannot use it.
     *
     * Answered with 500.
     */
    case StackUnusable = 'STACK-2';

    /**
     * Raised when the embedded stack is not intact.
     *
     * Answered with 500.
     */
    case StackNotEmbedded = 'STACK-3';

    /**
     * Raised when lemonfiber has nowhere to write the stack.
     *
     * Answered with 500.
     */
    case StackNotSetUp = 'STACK-4';

    /**
     * Raised when the stack could not be written to disk.
     *
     * Answered with 500.
     */
    case StackNotWritten = 'STACK-5';

    /**
     * Raised when a manifest parses and breaks the contract.
     *
     * Answered with 500.
     */
    case StackInvalid = 'STACK-6';

    /**
     * Raised when a manifest is not TOML at all.
     *
     * Answered with 500.
     */
    case StackMalformed = 'STACK-7';

    /**
     * Raised when a manifest declares names this build does not know.
     *
     * Answered with 500.
     */
    case StackUnrecognised = 'STACK-8';

    /**
     * Raised when a stack names a newer lemonfiber than the one running.
     *
     * Answered with 500.
     */
    case StackNeedsNewer = 'STACK-9';

    /**
     * A capability was named that no service in this stack provides.
     *
     * Answered with 404.
     */
    case NoSuchFiller = 'WIRE-1';

    /**
     * The service named cannot do the thing it was asked to fill.
     *
     * Answered with 400.
     */
    case CannotFill = 'WIRE-2';

    /**
     * Nothing in this stack asks for the capability, so a choice would change nothing.
     *
     * Answered with 400.
     */
    case NothingAsks = 'WIRE-3';

    /**
     * The setting recording the choice could not be written.
     *
     * Answered with 500.
     */
    case ChoiceUnwritable = 'WIRE-4';

    /**
     * Raised when a choice answers an offer that was read against a wiring that has since moved.
     *
     * Answered with 400.
     */
    case WiringMoved = 'WIRE-5';

    /**
     * Raised when the reason given for a choice is longer than a reason may be, or holds a line break or another control character.
     *
     * Answered with 400.
     */
    case Unreasonable = 'WIRE-6';

    /**
     * The case a code names, or none where there is no code or one this list does not name.
     */
    public static function of(?string $code): ?self
    {
        return $code === null ? null : self::tryFrom($code);
    }

    /**
     * The status a refusal carrying this code is answered with.
     */
    public function status(): int
    {
        return match ($this) {
            self::NotAdmitted => 403,
            self::Elsewhere => 403,
            self::NotYours => 403,
            self::Unconfirmed => 403,
            self::NotThePassword => 401,
            self::TooManyAttempts => 429,
            self::NotAPassword => 400,
            self::KeyInTheClear => 403,
            self::NotForAKey => 403,
            self::NoSuchAction => 404,
            self::MissingArgument => 400,
            self::UnrecognisedArgument => 400,
            self::UnwantedArgument => 400,
            self::ArgumentsTogether => 400,
            self::NotArguments => 400,
            self::NoSuchJob => 404,
            self::NotAnAnswer => 400,
            self::NoEndpoint => 404,
            self::WrongMethod => 405,
            self::NotAKeyRequest => 400,
            self::AnotherReading => 400,
            self::OfferMoved => 400,
            self::Unreadable => 404,
            self::Refused => 400,
            self::Unrecorded => 500,
            self::Already => 400,
            self::Nowhere => 500,
            self::Unwritable => 500,
            self::Unrecordable => 500,
            self::Unproved => 500,
            self::NothingToRemove => 404,
            self::NothingToUpdate => 404,
            self::Stuck => 500,
            self::Answered => 400,
            self::TwoSources => 400,
            self::SourceOff => 400,
            self::Unfetched => 500,
            self::NoRevision => 404,
            self::CatalogueOff => 400,
            self::CatalogueUnreachable => 500,
            self::SignatureUnverified => 500,
            self::CatalogueUnreadable => 500,
            self::NotCatalogued => 404,
            self::NotAsReviewed => 500,
            self::SpelledAlike => 400,
            self::PluginOfferMoved => 400,
            self::Unapproved => 400,
            self::AnotherPlugin => 400,
            self::Occupied => 400,
            self::CatalogueReplaced => 500,
            self::NewestUnkept => 500,
            self::SchemeRefused => 400,
            self::AddressRefused => 400,
            self::Unwanted => 400,
            self::Repeated => 400,
            self::NoSuchRead => 404,
            self::NoTerm => 400,
            self::NotASeason => 400,
            self::NoSetting => 400,
            self::NoMember => 400,
            self::NoShelfWithoutAMember => 400,
            self::NotACount => 400,
            self::TooManyAtOnce => 400,
            self::NoSuchGroup => 400,
            self::NoSuchRemoval => 400,
            self::NoUpdateObject => 400,
            self::NotALineCount => 400,
            self::NotAChoice => 400,
            self::MemberAndDefaults => 400,
            self::Stale => 400,
            self::MovedOn => 400,
            self::Unrenderable => 500,
            self::NoJobName => 500,
            self::AnotherOffer => 400,
            self::StackUnreadable => 500,
            self::StackUnusable => 500,
            self::StackNotEmbedded => 500,
            self::StackNotSetUp => 500,
            self::StackNotWritten => 500,
            self::StackInvalid => 500,
            self::StackMalformed => 500,
            self::StackUnrecognised => 500,
            self::StackNeedsNewer => 500,
            self::NoSuchFiller => 404,
            self::CannotFill => 400,
            self::NothingAsks => 400,
            self::ChoiceUnwritable => 500,
            self::WiringMoved => 400,
            self::Unreasonable => 400,
        };
    }
}
