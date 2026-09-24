<?php

// Generated from contract/web-api.contract.json. Do not edit.
// Source: f2519ad9ccd4d7d4ac40eea3306289a6ee400e50, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\Payload;
use Lemonfiber\Sdk\Exception\UnexpectedKind;

/**
 * The `migration` envelope, shaped as the contract describes it.
 *
 * @phpstan-type Data array{beside: list<array{from: int, service: string, to: int}>, carrying: list<array{backup_first: bool, because: string, existing: string, ours: string, refused: bool, service: string, verdict: string}>, conflicts: list<array{held_by: string, port: int, wanted_by: string}>, linking?: array{because: string, cost: string, filesystems: list<string>, forced: bool, links: bool, remedy: string}|null, modes: list<array{disturbs: bool, mode: string, preselected: bool, what: string}>, not_carried: list<array{because: string, what: string}>, read: bool, standing: list<array{project: string, services: list<array{adoptable: bool, ports: list<int>, running: bool, service: string}>}>, unsupported: list<array{because: string, what: string}>}
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

        return new Envelope($envelope->apiVersion, $envelope->kind, $data);
    }
}
