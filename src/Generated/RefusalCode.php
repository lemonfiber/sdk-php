<?php

// Generated from contract/web-api.contract.json. Do not edit.
// Source: 905074462b0d8eafa2de9f9d9f259c1feb0c7bb9, api_version 1.
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
     * Raised when a request carried no token or session this run admits.
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
     * Raised where whether to keep reading is neither true nor false.
     *
     * Answered with 400.
     */
    case NotAChoice = 'READ-15';

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
            self::AnotherReading => 400,
            self::OfferMoved => 400,
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
            self::Stale => 400,
            self::MovedOn => 400,
            self::Unrenderable => 500,
            self::NoJobName => 500,
            self::AnotherOffer => 400,
        };
    }
}
