<?php

// Generated from contract/web-api.contract.json. Do not edit.
// Source: v0.11.0, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\Payload;
use Lemonfiber\Sdk\Exception\UnexpectedKind;

/**
 * The `status` envelope, shaped as the contract describes it.
 *
 * @phpstan-type Data array{condition: 'inactive'|'degraded'|'partial'|'active', forms: list<string>, services: list<array{criticality: 'critical'|'core'|'important'|'enhancing'|'optional', depends_on: list<string>, exit?: int|null, id: string, name: string, profile: string, state: 'failed'|'crash-looping'|'unhealthy'|'absent'|'stopped'|'starting'|'running'|'healthy'|'host-managed'}>}
 */
final class StatusEnvelope
{
    /**
     * The kind an envelope must carry to be read as this one.
     */
    public const Kind KIND = Kind::Status;

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
