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
 * The `held` envelope, shaped as the contract describes it.
 *
 * @phpstan-type Data array{available: bool, findings: list<string>, holdings: list<array{id: string, medium: 'film'|'series'|'other', title: string, year?: int|null}>, id: string, member: string}
 */
final class HeldEnvelope
{
    /**
     * The kind an envelope must carry to be read as this one.
     */
    public const Kind KIND = Kind::Held;

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
