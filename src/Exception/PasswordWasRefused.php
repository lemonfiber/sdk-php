<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Exception;

use RuntimeException;

/**
 * The password offered is not the one this machine admits.
 *
 * Its own type rather than a {@see RequestFailed} carrying `401`, because the
 * surface goes out of its way to make this answer distinguishable and a client
 * that flattened it would throw that away. Every other refusal there answers
 * `403`, which means *nothing you could send would help* — true of a missing
 * token and false of a wrong password. A caller that cannot tell them apart
 * cannot know whether offering a login is worth anything.
 *
 * **It carries nothing about what was sent.** There is one sentence for a wrong
 * password and for a right one where no password is set, and that is the
 * server's decision rather than an omission here: telling them apart would tell
 * somebody guessing whether there is anything to guess at.
 */
final class PasswordWasRefused extends RuntimeException implements Problem
{
    /**
     * What a developer reads.
     *
     * One literal rather than a concatenation, which is the shape the rest of
     * this namespace uses and is also the only shape a mutation run can settle:
     * two adjacent literals joined by `.` are three mutants — drop the left,
     * drop the right, swap them — and nothing can kill them short of asserting
     * a developer-facing sentence word for word, which is a test that fails
     * every time somebody improves the wording.
     */
    private const string SENTENCE = 'That is not the password for this machine. Nothing was opened. If a password has never been set on this machine, no password will open it — set one there first.';

    public static function atTheDoor(): self
    {
        return new self(self::SENTENCE);
    }
}
