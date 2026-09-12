<?php

// Generated from contract/web-api.contract.json. Do not edit.
// Source: 387f0c83c55614ac3839bb21e88dd0c3a2feb50f, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\Payload;
use Lemonfiber\Sdk\Exception\UnexpectedKind;

/**
 * The `credentials` envelope, shaped as the contract describes it.
 *
 * @phpstan-type Data array{held: list<array{advisory?: string|null, consumers: list<string>, fingerprint?: string|null, location: string, name: string, origin: 'operator'|'service'|'lemonfiber', setting: string, state: 'absent'|'active'|'stale'|'invalid'|'rotating'|'superseded'}>, protection: array{against: list<string>, not_against: list<string>, summary: string}, revealed?: array{name: string, value?: string|null, warning: string}|null, rotated?: array{consumers: list<array{consumer: string, reach: array{reach: 'updated'}|array{detail: string, reach: 'pending'}|array{detail: string, reach: 'failed'}}>, credential: string, settled: array{observed: string, settled: 'replaced'}|array{detail: string, settled: 'refused'}|array{detail: string, settled: 'unproven'}|array{known: list<string>, settled: 'unknown'}|array{detail: string, settled: 'elsewhere'}}|null}
 */
final class CredentialsEnvelope
{
    /**
     * The kind an envelope must carry to be read as this one.
     */
    public const Kind KIND = Kind::Credentials;

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
