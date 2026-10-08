<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: 65103564328269478a863ae41bbf527f6f6c5f1e, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

/**
 * Every code the contract lists a refusal as carrying.
 *
 * A code this list does not name is read by its status alone. What the
 * contract says of each code is in {@see RefusalStatus} and
 * {@see RefusalDescription}.
 */
enum RefusalCode: string
{
    case NotAdmitted = 'ADMIT-4';
    case Elsewhere = 'ADMIT-5';
    case NotYours = 'ADMIT-6';
    case Unconfirmed = 'ADMIT-7';
    case NotThePassword = 'ADMIT-8';
    case TooManyAttempts = 'ADMIT-9';
    case NotAPassword = 'ADMIT-10';
    case KeyInTheClear = 'ADMIT-11';
    case NotForAKey = 'ADMIT-12';
    case NoSuchAction = 'ASK-1';
    case MissingArgument = 'ASK-2';
    case UnrecognisedArgument = 'ASK-3';
    case UnwantedArgument = 'ASK-4';
    case ArgumentsTogether = 'ASK-5';
    case NotArguments = 'ASK-6';
    case NoSuchJob = 'ASK-7';
    case NotAnAnswer = 'ASK-8';
    case NoEndpoint = 'ASK-9';
    case WrongMethod = 'ASK-10';
    case NotAKeyRequest = 'ASK-11';
    case NotAnIdempotencyKey = 'ASK-12';
    case IdempotencyKeyReused = 'ASK-13';
    case AnotherReading = 'GONE-2';
    case OfferMoved = 'MIGRATE-1';
    case Unreadable = 'PLUGIN-2';
    case Refused = 'PLUGIN-3';
    case Unrecorded = 'PLUGIN-4';
    case Already = 'PLUGIN-5';
    case Nowhere = 'PLUGIN-6';
    case Unwritable = 'PLUGIN-7';
    case Unrecordable = 'PLUGIN-8';
    case Unproved = 'PLUGIN-9';
    case NothingToRemove = 'PLUGIN-10';
    case NothingToUpdate = 'PLUGIN-11';
    case Stuck = 'PLUGIN-12';
    case Answered = 'PLUGIN-13';
    case TwoSources = 'PLUGIN-14';
    case SourceOff = 'PLUGIN-15';
    case Unfetched = 'PLUGIN-16';
    case NoRevision = 'PLUGIN-17';
    case CatalogueOff = 'PLUGIN-18';
    case CatalogueUnreachable = 'PLUGIN-19';
    case SignatureUnverified = 'PLUGIN-20';
    case CatalogueUnreadable = 'PLUGIN-21';
    case NotCatalogued = 'PLUGIN-22';
    case NotAsReviewed = 'PLUGIN-23';
    case SpelledAlike = 'PLUGIN-24';
    case PluginOfferMoved = 'PLUGIN-25';
    case Unapproved = 'PLUGIN-26';
    case AnotherPlugin = 'PLUGIN-27';
    case Occupied = 'PLUGIN-28';
    case CatalogueReplaced = 'PLUGIN-29';
    case NewestUnkept = 'PLUGIN-30';
    case SchemeRefused = 'PLUGIN-31';
    case AddressRefused = 'PLUGIN-32';
    case HeaderNamed = 'PLUGIN-33';
    case Unwanted = 'READ-1';
    case Repeated = 'READ-2';
    case NoSuchRead = 'READ-3';
    case NoTerm = 'READ-4';
    case NotASeason = 'READ-5';
    case NoSetting = 'READ-6';
    case NoMember = 'READ-7';
    case NoShelfWithoutAMember = 'READ-8';
    case NotACount = 'READ-9';
    case TooManyAtOnce = 'READ-10';
    case NoSuchGroup = 'READ-11';
    case NoSuchRemoval = 'READ-12';
    case NoUpdateObject = 'READ-13';
    case NotALineCount = 'READ-14';
    case NotAChoice = 'READ-15';
    case MemberAndDefaults = 'READ-16';
    case Stale = 'REPAIR-1';
    case MovedOn = 'RESTORE-11';
    case Unrenderable = 'SERVE-6';
    case NoJobName = 'SERVE-7';
    case Unanswered = 'SERVE-8';
    case AnotherOffer = 'SPACE-6';
    case StackUnreadable = 'STACK-1';
    case StackUnusable = 'STACK-2';
    case StackNotEmbedded = 'STACK-3';
    case StackNotSetUp = 'STACK-4';
    case StackNotWritten = 'STACK-5';
    case StackInvalid = 'STACK-6';
    case StackMalformed = 'STACK-7';
    case StackUnrecognised = 'STACK-8';
    case StackNeedsNewer = 'STACK-9';
    case NoSuchFiller = 'WIRE-1';
    case CannotFill = 'WIRE-2';
    case NothingAsks = 'WIRE-3';
    case ChoiceUnwritable = 'WIRE-4';
    case WiringMoved = 'WIRE-5';
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
        return RefusalStatus::of($this);
    }

    /**
     * The registry's own line about this code.
     */
    public function description(): string
    {
        return RefusalDescription::of($this);
    }
}
