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
 * The `copying` envelope, shaped as the contract describes it.
 *
 * @phpstan-import-type Instant from Shapes
 * @phpstan-import-type Tree from Shapes
 * @phpstan-type CopiedScope array{scope: 'whole_stack'}|array{name: string, scope: 'service'}|array{project?: string|null, scope: 'existing', trees?: list<Tree>|null}
 * @phpstan-type CopyWhat 'backup'|'restore'
 * @phpstan-type CopyingReport array{running?: RunningCopy|null}
 * @phpstan-type RunningCopy array{job?: string|null, part: string, scope: CopiedScope, started: Instant, what: CopyWhat, whole: int, written: int, state: 'working'}|array{job?: string|null, part: string, scope: CopiedScope, started: Instant, what: CopyWhat, whole: int, written: int, limit: int, state: 'paced'}|array{job?: string|null, part: string, scope: CopiedScope, started: Instant, what: CopyWhat, whole: int, written: int, state: 'stalled', wrote: Instant}
 * @phpstan-type Data CopyingReport
 */
final class CopyingEnvelope
{
    /**
     * The kind an envelope must carry to be read as this one.
     */
    public const Kind KIND = Kind::Copying;

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
