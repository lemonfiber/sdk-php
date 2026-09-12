<?php

// Generated from contract/web-api.contract.json. Do not edit.
// Source: b74e46e28c96aa19bd9f3712ae9b6a2b6429a058, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\Payload;
use Lemonfiber\Sdk\Exception\UnexpectedKind;

/**
 * The `config` envelope, shaped as the contract describes it.
 *
 * @phpstan-type Data array{changed: bool, consequence?: string|null, rehearsed: bool, review?: array{change: array{cost: 'cheap'|'consequential', from?: string|null, key: string, to: string}, findings?: array{active: list<array{name: string, progress: int, protocol: string}>, edited?: array{found: string, secret: bool, wrote: string}|null, keeps: list<string>, library: list<array{because: string, carried: bool, host?: string|null, path: string, service: string}>, opens: list<array{because: string, setting?: string|null, what: string}>, stops: list<string>}, proof?: array{observed: string, outcome: 'valid'}|array{detail: string, outcome: 'rejected'}|array{detail: string, outcome: 'unreachable'}|array{detail: string, outcome: 'degraded'}|null, refusal?: string|null, stance: 'unchanged'|'pending'|'blocked'|'applied'}|null, settings: list<array{key: string, secret: bool, value: string}>}
 */
final class ConfigEnvelope
{
    /**
     * The kind an envelope must carry to be read as this one.
     */
    public const Kind KIND = Kind::Config;

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
