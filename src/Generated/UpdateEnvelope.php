<?php

// Generated from contract/web-api.contract.json. Do not edit.
// Source: 9fd0586162913789261e9b17399655655f1de60a, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\Payload;
use Lemonfiber\Sdk\Exception\UnexpectedKind;

/**
 * The `update` envelope, shaped as the contract describes it.
 *
 * @phpstan-type Data array{applied: list<array{detail?: string|null, ending: 'updated'|'not-fetched'|'not-started'|'not-reached', from: string, reversal: 'rollback'|'restore', service: string, to: string}>, backup?: string|null, changelog: array{releases: list<array{delivers?: string|null, patches?: string|null, released_on?: string|null, user_facing: bool, version: string, withdrawn?: string|null}>, requirements: array<string, array{feature: string, shipped_in: list<string>, url?: string|null, withdrawn?: bool}>, running?: array{carried?: string|null, delivers?: string|null, groups: list<array{entries: list<array{reference?: string|null, requirements: list<string>, summary: string}>, title: string}>, patches?: string|null, released_on?: string|null, tag: string, user_facing: bool, version: string, withdrawn?: string|null}|null, state: 'current'|'pending'|'stale'}, changes: list<array{because: string, current: string, irreversible: bool, jump: 'major'|'minor'|'patch'|'untellable', refused: bool, service: string, target: string}>, confirmed: bool, halted?: string|null, in_flight: list<string>, stack_edits: list<array{diff: string, path: string}>, state: 'current'|'updates-available'|'updated'|'partial'|'failed'}
 */
final class UpdateEnvelope
{
    /**
     * The kind an envelope must carry to be read as this one.
     */
    public const Kind KIND = Kind::Update;

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
