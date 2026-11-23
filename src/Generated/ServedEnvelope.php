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
 * The `served` envelope, shaped as the contract describes it.
 *
 * @phpstan-import-type Instant from Shapes
 * @phpstan-type Described array{fingerprint: string, issuer: string, issuer_cut: bool, names: list<string>, names_more: int, self_signed: bool, subject: string, subject_cut: bool, valid_from: Instant, valid_until: Instant}
 * @phpstan-type ServedReport array{certificate?: Described|null, encrypted: bool, named?: string|null, network: bool, port: int, unrecorded?: string|null}
 * @phpstan-type Data ServedReport
 */
final class ServedEnvelope
{
    /**
     * The kind an envelope must carry to be read as this one.
     */
    public const Kind KIND = Kind::Served;

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
