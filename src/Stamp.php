<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk;

use function count;

use DateTimeImmutable;
use DateTimeZone;

use function explode;
use function preg_match;
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
 * **The calendar does the refusing, and it is asked by writing the moment back
 * out.** `createFromFormat` is lenient in a way no pattern in front of it can
 * fix: February 30th is answered with March 2nd, hour 25 with one in the
 * morning, and the return value is a perfectly good date either way. Formatting
 * the result with the same format and comparing it to what arrived catches both
 * that and text the format could not read at all — one question, asked once,
 * with no branch that cannot be reached.
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

        if ($read === false || $read->format(self::FIELDS) !== $bare) {
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
     * Asked with a pattern rather than `ctype_digit`, which would make this
     * library require `ext-ctype` for one question about one field. PCRE is
     * already a dependency of everything, and a client that has to be told to
     * install an extension for a timestamp is a client that is harder to adopt
     * than it needs to be.
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

        return count($parts) === 2 && preg_match('/^\d+$/', $parts[1]) === 1 ? $parts[0] : null;
    }

}
