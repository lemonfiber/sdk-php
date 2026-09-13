<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Exception;

use RuntimeException;

use function sprintf;

/**
 * The door has stopped answering for a while, after too many wrong passwords.
 *
 * Told apart from {@see PasswordWasRefused} because the remedy is the opposite
 * one: a wrong password is answered by trying another, and this is answered by
 * waiting — and by *not* trying another, since another attempt is what extends
 * the wait. A client that reported both as "wrong password" would have the
 * operator typing carefully into a door that is not listening.
 *
 * It carries the wait where the surface named one. `Retry-After` is a header
 * the server sends for exactly this, and the seconds are what a screen needs in
 * order to say something better than "try again later".
 */
final class TooManyAttempts extends RuntimeException implements Problem
{
    /**
     * What a developer reads where the answer named a wait.
     *
     * One literal rather than a concatenation, for the reason
     * {@see PasswordWasRefused} gives: adjacent literals joined by `.` are
     * mutants nothing can kill without pinning a sentence word for word.
     */
    private const string FOR_THIS_LONG = 'This machine has stopped answering password attempts for %d more seconds, after too many wrong ones. Nothing was opened. Waiting is what clears it; another attempt is what extends it.';

    /** Likewise, where it named none. */
    private const string FOR_SOME_UNSAID_TIME = 'This machine has stopped answering password attempts, after too many wrong ones, and did not say for how long. Nothing was opened. Waiting is what clears it; another attempt is what extends it.';

    private function __construct(private readonly ?int $seconds, string $message)
    {
        parent::__construct($message);
    }

    /** Where the answer said how long is left. */
    public static function forAnother(int $seconds): self
    {
        return new self($seconds, sprintf(self::FOR_THIS_LONG, $seconds));
    }

    /**
     * Where it did not.
     *
     * Separate from the above rather than defaulted to a number, because a
     * made-up wait is worse than none: a screen counting down from a guess is a
     * screen that tells the operator to try at a moment the door is still shut.
     */
    public static function forAWhile(): self
    {
        return new self(null, self::FOR_SOME_UNSAID_TIME);
    }

    /**
     * How many seconds the answer said were left, where it said.
     *
     * Null where the header was absent or unreadable, which a caller has to
     * handle rather than being handed a zero — zero means *try now*, and this
     * is the one state where that is exactly wrong.
     */
    public function seconds(): ?int
    {
        return $this->seconds;
    }
}
