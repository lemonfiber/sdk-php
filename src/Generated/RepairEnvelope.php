<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: 34570aa127f59a0c54d79ab5bf1c3022e39f89a9, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\Payload;
use Lemonfiber\Sdk\Exception\UnexpectedKind;

/**
 * The `repair` envelope, shaped as the contract describes it.
 *
 * @phpstan-import-type Remedy from Shapes
 * @phpstan-type Beyond array{check: string, remedy: Remedy}
 * @phpstan-type Mended array{outcome: RepairOutcome, repair: Repair}
 * @phpstan-type Repair array{check: string, does: string, effects: list<string>, reversible: bool}
 * @phpstan-type RepairOutcome array{outcome: 'fixed'}|array{outcome: 'fix_failed'}|array{leaving: string, outcome: 'stopped'}|array{outcome: 'declined'}|array{outcome: 'would_overwrite'}|array{outcome: 'unmanaged'}
 * @phpstan-type RepairReport array{acted: bool, agreement: string, beyond: list<Beyond>, mended: list<Mended>, offered: list<Repair>, rehearsed: bool}
 * @phpstan-type Data RepairReport
 */
final class RepairEnvelope
{
    /**
     * The kind an envelope must carry to be read as this one.
     */
    public const Kind KIND = Kind::Repair;

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
