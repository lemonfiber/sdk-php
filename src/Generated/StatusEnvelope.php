<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: 5a9496cec5089736f1f018ae55c551dd30cd6673, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\Payload;
use Lemonfiber\Sdk\Exception\UnexpectedKind;

/**
 * The `status` envelope, shaped as the contract describes it.
 *
 * @phpstan-import-type Condition from Shapes
 * @phpstan-import-type Filtered from Shapes
 * @phpstan-import-type Service from Shapes
 * @phpstan-import-type ServiceState from Shapes
 * @phpstan-import-type UnsupportedReport from Shapes
 * @phpstan-type Awaiting 'downloads'
 * @phpstan-type Disturbances array{restarting: TakesAway, starting: TakesAway, stopping: TakesAway, stopping_after_downloads: TakesAway, switching: TakesAway}
 * @phpstan-type StatusReport array{active_forms: list<string>, condition: Condition, disturbs: Disturbances, filtered: list<Filtered>, forms: list<string>, services: list<Service>, undeclared: list<Undeclared>, unsupported?: list<UnsupportedReport>}
 * @phpstan-type TakesAway array{bound: 'bounded', seconds: int}|array{bound: 'open-ended', until: Awaiting}
 * @phpstan-type Undeclared array{describes: string, id: string, state: ServiceState}
 * @phpstan-type Data StatusReport
 */
final class StatusEnvelope
{
    /**
     * The kind an envelope must carry to be read as this one.
     */
    public const Kind KIND = Kind::Status;

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
