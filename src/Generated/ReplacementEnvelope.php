<?php

// Generated from contract/web-api.contract.json. Do not edit.
// Source: 150bca10c47dd2a08076d6810ea09f8eb1a7c49e, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\Payload;
use Lemonfiber\Sdk\Exception\UnexpectedKind;

/**
 * The `replacement` envelope, shaped as the contract describes it.
 *
 * @phpstan-type Data array{project?: string|null, refusal?: string|null, stance: 'unchanged'|'pending'|'blocked'|'applied', still_running: list<string>, stopped: list<string>, would_stop: list<string>}
 */
final class ReplacementEnvelope
{
    /**
     * The kind an envelope must carry to be read as this one.
     */
    public const Kind KIND = Kind::Replacement;

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
