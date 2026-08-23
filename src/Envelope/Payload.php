<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Envelope;

use Lemonfiber\Sdk\Exception\UnexpectedKind;
use Lemonfiber\Sdk\Generated\Kind;

/**
 * What an envelope holds, reached through the kind that shapes it (ARCH-R63).
 *
 * The generated envelope classes are the callers: each names its own kind and
 * declares the type the contract gives that kind's payload. This is where the
 * kind on the wire is held against the kind being read for.
 */
final class Payload
{
    /**
     * @param  Envelope<mixed>  $envelope
     *
     * @throws UnexpectedKind
     */
    public static function under(Kind $kind, Envelope $envelope): mixed
    {
        if ($envelope->kind !== $kind->value) {
            throw UnexpectedKind::between($kind->value, $envelope->kind);
        }

        return $envelope->data;
    }
}
