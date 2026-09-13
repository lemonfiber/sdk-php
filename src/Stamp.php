<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk;

use function count;
use function ctype_digit;

use DateTimeImmutable;
use DateTimeZone;

use function explode;
use function str_ends_with;
use function substr;

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
 *
 * **The calendar does the refusing, not a pattern.** An earlier version checked
 * the shape with a regular expression first, which read well and was worse: it
 * made the calendar's own refusal unreachable, and unreachable code is code no
 * test can hold. Everything that is not the two decorations is handed to
 * `createFromFormat` exactly as it arrived, and what it will not read is what
 * this will not read.
 */
final readonly class Stamp
{
    /**
     * The bare shape, as the calendar reads it.
     *
     * Every field is spelled out, so nothing is filled in from the system clock
     * — which is what makes reading a stamp a function of its text rather than
     * of the moment it was read. Anything left over after it, an offset or a
     * word, is text the format does not account for and the parse refuses.
     */
    private const string FIELDS = 'Y-m-d\TH:i:s';

    /** The count of seconds this stamp names, or nothing where it names none. */
    public static function secondsIn(string $written): ?int
    {
        $bare = self::withoutDecoration($written);

        if ($bare === null) {
            return null;
        }

        $read = DateTimeImmutable::createFromFormat(self::FIELDS, $bare, new DateTimeZone('UTC'));

        if ($read === false || self::theCalendarObjected()) {
            return null;
        }

        return $read->getTimestamp();
    }

    /**
     * The same stamp with the two things the writer never emits taken off.
     *
     * A trailing `Z` is dropped because it says UTC, which is the frame this is
     * already in. A fraction is dropped because what is wanted is the second
     * something happened, and a service recording six decimal places is not
     * offering more certainty than that.
     *
     * Nothing where what follows the dot is not digits, which is the one place
     * this is deliberately stricter than dropping would be: `10:00:00.abc` is
     * not a stamp with a fraction on it, it is a stamp with something else on
     * it, and taking the front off would turn text nobody wrote into a moment.
     *
     * **An offset other than `Z` is not handled here at all**, and so reaches
     * the calendar with the offset still on it and is refused as text the
     * format does not account for. That is the same choice lemonfiber's own
     * reader makes: a stamp naming an offset is not this frame, and placing it
     * anyway would put a moment hours from where it says it is.
     */
    private static function withoutDecoration(string $written): ?string
    {
        $rest = str_ends_with($written, 'Z') ? substr($written, 0, -1) : $written;
        $parts = explode('.', $rest);

        if (count($parts) === 1) {
            return $rest;
        }

        return count($parts) === 2 && ctype_digit($parts[1]) ? $parts[0] : null;
    }

    /**
     * Whether the calendar had to move the moment in order to accept it.
     *
     * `createFromFormat` answers a February 30th with March 2nd and a warning
     * rather than a refusal, and a session that expires two days after it says
     * it does is worse than one that could not be read at all. The stack bounds
     * every field for this reason; this is the same refusal on the near side.
     *
     * Asked as *did it say anything*, rather than by counting warnings: since
     * PHP 8.2 a parse with nothing to report answers `false` here, so the
     * count is either absent or at least one and comparing it to a number would
     * be arithmetic on a value that only ever takes one interesting shape.
     */
    private static function theCalendarObjected(): bool
    {
        return DateTimeImmutable::getLastErrors() !== false;
    }
}
