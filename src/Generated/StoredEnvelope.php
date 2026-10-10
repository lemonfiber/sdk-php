<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: 640f134910d8eecc146aa1c2531c4c7d2b0f177f, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\Payload;
use Lemonfiber\Sdk\Exception\UnexpectedKind;

/**
 * The `stored` envelope, shaped as the contract describes it.
 *
 * @phpstan-type Kept array{at: string, secret: bool, what: string, why: string}
 * @phpstan-type Root array{at: string, what: string}
 * @phpstan-type Stored array{beside: list<StoredBeside>, kept: list<Kept>, rehearsed: bool, removal: StoredRemoval, roots: list<Root>}
 * @phpstan-type StoredBeside array{what: string, why: string}
 * @phpstan-type StoredLeft array{at: string, why: string}
 * @phpstan-type StoredRemoval array{state: 'not-asked'}|array{state: 'unconfirmed'}|array{gone: list<string>, left: list<StoredLeft>, state: 'done'}
 * @phpstan-type Data Stored
 */
final class StoredEnvelope
{
    /**
     * The kind an envelope must carry to be read as this one.
     */
    public const Kind KIND = Kind::Stored;

    /**
     * The same envelope with its payload typed by its kind.
     *
     * @param  Envelope<mixed>  $envelope
     * @return Envelope<Data>
     *
     * @throws UnexpectedKind
     */
    public static function in(Envelope $envelope): Envelope
    {
        /** @var Data $data */
        $data = Payload::under(self::KIND, $envelope);

        return new Envelope($envelope->apiVersion, $envelope->kind, $data, $envelope->host);
    }
}
