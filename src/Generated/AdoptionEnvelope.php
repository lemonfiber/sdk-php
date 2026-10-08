<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: d9679f541083537834005ea1994bd4f4135a60a3, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\Payload;
use Lemonfiber\Sdk\Exception\UnexpectedKind;

/**
 * The `adoption` envelope, shaped as the contract describes it.
 *
 * @phpstan-import-type CarryingReport from Shapes
 * @phpstan-import-type Stance from Shapes
 * @phpstan-type AdoptReport array{back_up: list<string>, backed_up?: string|null, project?: string|null, refusal?: string|null, rehearsed: bool, stance: Stance, upgrades: list<CarryingReport>}
 * @phpstan-type Data AdoptReport
 */
final class AdoptionEnvelope
{
    /**
     * The kind an envelope must carry to be read as this one.
     */
    public const Kind KIND = Kind::Adoption;

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
