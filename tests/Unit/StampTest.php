<?php

declare(strict_types=1);

use Lemonfiber\Sdk\Stamp;

/** The moment every case here names, as a count of seconds. */
const AT_TEN = 1789380000;

it('reads the shape every lemonfiber service writes', function (): void {
    expect(Stamp::secondsIn('2026-09-14T10:00:00'))->toBe(AT_TEN);
});

it('reads a stamp that says UTC out loud the same way', function (): void {
    // The writer never puts a `Z` on one, but the stack's own reader takes it
    // and it names the frame this is already in. A client stricter than the
    // service it talks to breaks on the day that service writes what it has
    // always said it would accept.
    expect(Stamp::secondsIn('2026-09-14T10:00:00Z'))->toBe(AT_TEN);
});

it('reads a fraction of a second and drops it', function (): void {
    // What is wanted is the second something happened. A service recording six
    // decimal places is not offering more certainty than that.
    expect(Stamp::secondsIn('2026-09-14T10:00:00.123456'))->toBe(AT_TEN)
        ->and(Stamp::secondsIn('2026-09-14T10:00:00.5Z'))->toBe(AT_TEN);
});

it('refuses a dot with something other than digits after it', function (): void {
    // Not a stamp with a fraction on it — a stamp with something else on it.
    // Dropping the tail the way a real fraction is dropped would turn text
    // nobody wrote into a moment.
    expect(Stamp::secondsIn('2026-09-14T10:00:00.abc'))->toBeNull()
        ->and(Stamp::secondsIn('2026-09-14T10:00:00.'))->toBeNull()
        ->and(Stamp::secondsIn('2026-09-14T10:00:00.1.2'))->toBeNull();
});

it('refuses a stamp that names an offset of its own', function (): void {
    // Not this frame, and guessing at it would place the moment hours from
    // where it says it is — so it is not read at all rather than read wrongly.
    expect(Stamp::secondsIn('2026-09-14T10:00:00+02:00'))->toBeNull();
});

it('refuses a day the calendar does not have', function (): void {
    // The date library answers this with March 2nd and a warning rather than a
    // refusal, and a session that ends two days after it says it does is worse
    // than one that could not be read at all.
    expect(Stamp::secondsIn('2026-02-30T10:00:00'))->toBeNull();
});

it('refuses a clock field past the end of its range', function (): void {
    expect(Stamp::secondsIn('2026-09-14T25:00:00'))->toBeNull()
        ->and(Stamp::secondsIn('2026-09-14T10:61:00'))->toBeNull();
});

it('refuses text that is not a stamp at all', function (): void {
    expect(Stamp::secondsIn(''))->toBeNull()
        ->and(Stamp::secondsIn('soon'))->toBeNull()
        ->and(Stamp::secondsIn('2026-09-14'))->toBeNull();
});

it('refuses a stamp with anything after it', function (): void {
    // Trailing text means the answer is not the shape it appears to be, and
    // reading the front of it would be inventing an ending nobody sent.
    expect(Stamp::secondsIn('2026-09-14T10:00:00 and later'))->toBeNull();
});

it('reads the epoch itself, which is a moment like any other', function (): void {
    expect(Stamp::secondsIn('1970-01-01T00:00:00'))->toBe(0);
});
