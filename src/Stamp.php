<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk;

use DateTimeImmutable;
use DateTimeZone;

use function preg_match;

/**
 * An instant as the services write one, read back as a count of seconds.
 *
 * Every instant this product writes is ISO-8601 in UTC — the date, then the
 * time to the second — and this is the one place a client turns one back into a
 * number. Named rather than inlined where it is needed because the shape is
 * product-wide rather than admission's: `until` is the first stamp to cross
 * this boundary and it will not be the last, and a second copy of this parse is
 * a second chance for the two to disagree about what the stack sent.
 *
 * **Seconds rather than a `DateTimeImmutable`.** A date object carries a
 * timezone, and a timezone is a rendering decision belonging to whoever shows
 * something to somebody. Nothing a caller decides with this — whether a session
 * has expired — changes with where the reader is standing, so what crosses the
 * boundary carries no answer to a question nobody asked.
 *
 * **Read as lemonfiber's own reader reads one.** The stack writes the bare form
 * and accepts two decorations on top of it, so this accepts the same two: a
 * client stricter than the service it talks to breaks on the day that service
 * starts writing something it has always said it would accept.
 */
final readonly class Stamp
{
    /**
     * The shape a stamp must have, with the two optional decorations on it.
     *
     * A trailing `Z` is read and dropped because it says UTC, which is the
     * frame this is already in. A fraction is read and dropped because what is
     * wanted is the second something happened, and a service recording six
     * decimal places is not offering more certainty than that.
     *
     * **An offset other than `Z` is deliberately not matched.** A stamp naming
     * one is not this frame, and guessing would place a moment hours from where
     * it says it is — so it is not read at all rather than read wrongly, which
     * is the same choice lemonfiber's own reader makes.
     */
    private const string SHAPE = '/^(\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2})(\.\d+)?Z?$/';

    /**
     * The bare shape, as the calendar reads it.
     *
     * Every field is spelled out, so nothing is filled in from the system clock
     * — which is what makes reading a stamp a function of its text rather than
     * of the moment it was read.
     */
    private const string FIELDS = 'Y-m-d\TH:i:s';

    /** The count of seconds this stamp names, or nothing where it names none. */
    public static function secondsIn(string $written): ?int
    {
        if (preg_match(self::SHAPE, $written, $found) !== 1) {
            return null;
        }

        $read = DateTimeImmutable::createFromFormat(self::FIELDS, $found[1], new DateTimeZone('UTC'));

        return $read === false || self::rolledOver() ? null : $read->getTimestamp();
    }

    /**
     * Whether the calendar had to move the moment to accept it.
     *
     * `createFromFormat` answers a February 30th with March 2nd and a warning
     * rather than a refusal, and a session that expires two days after it says
     * it does is worse than one that could not be read at all. The stack bounds
     * every field for this reason; this is the same refusal on the near side.
     */
    private static function rolledOver(): bool
    {
        $complaints = DateTimeImmutable::getLastErrors();

        return $complaints !== false && $complaints['warning_count'] > 0;
    }
}
