<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: dc6670838b58c27ef1b1c64966f911492ff92c66, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\Payload;
use Lemonfiber\Sdk\Exception\UnexpectedKind;

/**
 * The `update` envelope, shaped as the contract describes it.
 *
 * @phpstan-import-type Notes from Shapes
 * @phpstan-import-type StackEdit from Shapes
 * @phpstan-type Ending 'updated'|'not-fetched'|'not-started'|'not-reached'
 * @phpstan-type Jump 'major'|'minor'|'patch'|'untellable'
 * @phpstan-type StackUpdateReport array{applied: list<UpdateApplied>, backup?: string|null, changelog: Notes, changes: list<UpdateChange>, confirmed: bool, halted?: string|null, in_flight: list<string>, offer: string, rehearsed: bool, stack_edits: list<StackEdit>, state: UpdateState}
 * @phpstan-type UpdateApplied array{detail?: string|null, ending: Ending, from: string, reversal: UpdateReversal, service: string, to: string}
 * @phpstan-type UpdateChange array{because: string, current: string, irreversible: bool, jump: Jump, refused: bool, service: string, target: string}
 * @phpstan-type UpdateReversal 'rollback'|'restore'
 * @phpstan-type UpdateState 'current'|'updates-available'|'updated'|'partial'|'failed'
 * @phpstan-type Data StackUpdateReport
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

        return new Envelope($envelope->apiVersion, $envelope->kind, $data, $envelope->host);
    }
}
