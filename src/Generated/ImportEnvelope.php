<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: 0e9fbff3d7b423c54967af31b0b77d083e37b0a0, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\Payload;
use Lemonfiber\Sdk\Exception\UnexpectedKind;

/**
 * The `import` envelope, shaped as the contract describes it.
 *
 * @phpstan-import-type Stance from Shapes
 * @phpstan-import-type UnsupportedReport from Shapes
 * @phpstan-type ImportReport array{carried: list<RecordReport>, not_carried: list<UnsupportedReport>, project?: string|null, refusal?: string|null, rehearsed: bool, stance: Stance, would_carry: list<RecordReport>}
 * @phpstan-type RecordReport array{kind: string, name: string, service: string}
 * @phpstan-type Data ImportReport
 */
final class ImportEnvelope
{
    /**
     * The kind an envelope must carry to be read as this one.
     */
    public const Kind KIND = Kind::Import;

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
