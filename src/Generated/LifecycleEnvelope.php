<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: 65103564328269478a863ae41bbf527f6f6c5f1e, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\Payload;
use Lemonfiber\Sdk\Exception\UnexpectedKind;

/**
 * The `lifecycle` envelope, shaped as the contract describes it.
 *
 * @phpstan-import-type Condition from Shapes
 * @phpstan-import-type ConflictReport from Shapes
 * @phpstan-import-type Plan from Shapes
 * @phpstan-import-type Service from Shapes
 * @phpstan-import-type StackEdit from Shapes
 * @phpstan-type LifecycleReport array{action: string, command: list<string>, condition?: Condition|null, forwarding?: string|null, held?: string|null, plan: Plan, port_conflicts?: list<ConflictReport>, rehearsed: bool, services: list<Service>, stack_edits: list<StackEdit>, status?: int|null, switched?: Switched|null}
 * @phpstan-type Switched array{kept: list<string>, started: list<string>, stop_command?: list<string>|null, stopped: list<string>}
 * @phpstan-type Data LifecycleReport
 */
final class LifecycleEnvelope
{
    /**
     * The kind an envelope must carry to be read as this one.
     */
    public const Kind KIND = Kind::Lifecycle;

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
