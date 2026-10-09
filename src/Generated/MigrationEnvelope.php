<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: ebe81f0581db0d09c3025f7f745d5fa71883085a, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\Payload;
use Lemonfiber\Sdk\Exception\UnexpectedKind;

/**
 * The `migration` envelope, shaped as the contract describes it.
 *
 * @phpstan-import-type CarryingReport from Shapes
 * @phpstan-import-type ConflictReport from Shapes
 * @phpstan-import-type MovedReport from Shapes
 * @phpstan-import-type UnsupportedReport from Shapes
 * @phpstan-type LinkingReport array{because: string, cost: string, filesystems: list<string>, forced: bool, links: bool, remedy: string}
 * @phpstan-type MigrationReport array{beside: list<MovedReport>, carrying: list<CarryingReport>, conflicts: list<ConflictReport>, linking?: LinkingReport|null, modes: list<ModeReport>, not_carried: list<UnsupportedReport>, read: bool, standing: list<StandingReport>, unsupported: list<UnsupportedReport>}
 * @phpstan-type ModeReport array{disturbs: bool, mode: string, preselected: bool, what: string}
 * @phpstan-type OccupantReport array{adoptable: bool, ports: list<int>, running: bool, service: string}
 * @phpstan-type StandingReport array{project: string, services: list<OccupantReport>}
 * @phpstan-type Data MigrationReport
 */
final class MigrationEnvelope
{
    /**
     * The kind an envelope must carry to be read as this one.
     */
    public const Kind KIND = Kind::Migration;

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
