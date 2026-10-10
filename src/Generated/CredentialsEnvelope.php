<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: cf9a18239dd3e3d710ae8d9d9d8916ab255b799f, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\Payload;
use Lemonfiber\Sdk\Exception\UnexpectedKind;

/**
 * The `credentials` envelope, shaped as the contract describes it.
 *
 * @phpstan-import-type ValueOrigin from Shapes
 * @phpstan-type CredentialHeld array{advisory?: string|null, consumers: list<string>, fingerprint?: string|null, from: ValueOrigin, location: string, name: string, origin: Origin, setting: string, state: CredentialState}
 * @phpstan-type CredentialReach array{reach: 'updated'}|array{detail: string, reach: 'pending'}|array{detail: string, reach: 'failed'}
 * @phpstan-type CredentialState 'absent'|'active'|'stale'|'invalid'|'rotating'|'superseded'
 * @phpstan-type Inventory array{held: list<CredentialHeld>, protection: Protection, rehearsed: bool, revealed?: Revealed|null, rotated?: Rotation|null}
 * @phpstan-type Origin 'operator'|'service'|'lemonfiber'
 * @phpstan-type Propagation array{consumer: string, reach: CredentialReach}
 * @phpstan-type Protection array{against: list<string>, not_against: list<string>, summary: string}
 * @phpstan-type Revealed array{name: string, value?: string|null, warning: string}
 * @phpstan-type Rotation array{consumers: list<Propagation>, credential: string, settled: Settled}
 * @phpstan-type Settled array{observed: string, settled: 'replaced'}|array{detail: string, settled: 'refused'}|array{detail: string, settled: 'unproven'}|array{detail: string, settled: 'replaced-unproven'}|array{afterwards: list<string>, detail: string, location: string, settled: 'rehearsed'}|array{known: list<string>, settled: 'unknown'}|array{detail: string, settled: 'elsewhere'}
 * @phpstan-type Data Inventory
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

        return new Envelope($envelope->apiVersion, $envelope->kind, $data, $envelope->host);
    }
}
