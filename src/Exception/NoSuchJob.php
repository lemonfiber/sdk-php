<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Exception;

use RuntimeException;

use function sprintf;

/**
 * No work in this run goes by that name.
 *
 * Its own type rather than a {@see RequestFailed} carrying `404`, because the
 * two `404`s on that endpoint are different answers. Work that stopped on a
 * problem answers with the `error` envelope at whatever status the problem
 * warrants, and that can be `404` as well — a form nothing declares is absent
 * whichever door asked about it. A name nobody minted is answered in prose,
 * which is how the surface labels what it says in its own words, and it means
 * something a caller can act on: there is nothing to poll.
 *
 * **A name does not outlive the run that minted it.** Work in flight does not
 * survive the process doing it, so lemonfiber keeps no record of a name across
 * a restart and could not tell one it never minted from one a previous run did.
 * A client holding a name across a break in the connection is holding one that
 * may already mean nothing, and this is what it gets back when it does. That is
 * a reason to redeem a name promptly and to treat this as work that is over
 * rather than as a machine that is broken.
 */
final class NoSuchJob extends RuntimeException implements Problem
{
    public static function inThisRun(string $job): self
    {
        return new self(sprintf(
            'No work in this run goes by the name "%s". A name is minted by the run that starts the work and goes when that run goes, so one kept from before lemonfiber restarted names nothing now.',
            $job,
        ));
    }
}
