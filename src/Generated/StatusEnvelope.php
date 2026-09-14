<?php

// Generated from contract/web-api.contract.json. Do not edit.
// Source: 41067195202edaaa380b339294ccafae05e2e99d, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\Payload;
use Lemonfiber\Sdk\Exception\UnexpectedKind;

/**
 * The `status` envelope, shaped as the contract describes it.
 *
 * @phpstan-type Data array{condition: 'inactive'|'degraded'|'partial'|'active', disturbs: array{restarting: array{bound: 'bounded', seconds: int}|array{bound: 'open-ended', until: 'downloads'}, starting: array{bound: 'bounded', seconds: int}|array{bound: 'open-ended', until: 'downloads'}, stopping: array{bound: 'bounded', seconds: int}|array{bound: 'open-ended', until: 'downloads'}, stopping_after_downloads: array{bound: 'bounded', seconds: int}|array{bound: 'open-ended', until: 'downloads'}, switching: array{bound: 'bounded', seconds: int}|array{bound: 'open-ended', until: 'downloads'}}, forms: list<string>, services: list<array{criticality: 'critical'|'core'|'important'|'enhancing'|'optional', depends_on: list<string>, describes: string, exit?: int|null, id: string, name: string, profile: string, state: 'failed'|'crash-looping'|'unhealthy'|'absent'|'stopped'|'starting'|'running'|'healthy'|'host-managed'}>, undeclared: list<array{describes: string, id: string, state: 'failed'|'crash-looping'|'unhealthy'|'absent'|'stopped'|'starting'|'running'|'healthy'|'host-managed'}>}
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
