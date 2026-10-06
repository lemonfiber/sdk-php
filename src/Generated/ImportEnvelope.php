<?php

// Generated from contract/web-api.contract.json. Do not edit.
// Source: 9b0a1674c40c22a453816cf6f0cbed3227047526, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\Payload;
use Lemonfiber\Sdk\Exception\UnexpectedKind;

/**
 * The `import` envelope, shaped as the contract describes it.
 *
 * @phpstan-type Data array{carried: list<array{kind: string, name: string, service: string}>, not_carried: list<array{because: string, what: string}>, project?: string|null, refusal?: string|null, rehearsed: bool, stance: 'unchanged'|'pending'|'blocked'|'applied', would_carry: list<array{kind: string, name: string, service: string}>}
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

        return new Envelope($envelope->apiVersion, $envelope->kind, $data);
    }
}
