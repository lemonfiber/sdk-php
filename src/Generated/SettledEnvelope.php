<?php

// Generated from the contract vendored under contract/. Do not edit.
// Contract::SOURCE names its revision and Contract::API_VERSION its wire version.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\Payload;
use Lemonfiber\Sdk\Exception\UnexpectedKind;

/**
 * The `settled` envelope, shaped as the contract describes it.
 *
 * @phpstan-import-type Protocols from Shapes
 * @phpstan-import-type SetUp from Shapes
 * @phpstan-type ServiceUser array{from: UserFrom, gid: int, uid: int}
 * @phpstan-type SettledReport array{data_root?: string|null, protocols: Protocols, service_user: ServiceUser, setup: SetUp}
 * @phpstan-type UserFrom 'set'|'part-set'|'stack-owner'
 * @phpstan-type Data SettledReport
 */
final class SettledEnvelope
{
    /**
     * The kind an envelope must carry to be read as this one.
     */
    public const Kind KIND = Kind::Settled;

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

        return new Envelope($envelope->apiVersion, $envelope->kind, $data, $envelope->host, $envelope->job);
    }
}
