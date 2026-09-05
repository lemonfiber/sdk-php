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
 * The `wizard` envelope, shaped as the contract describes it.
 *
 * @phpstan-type Data array{asks: bool, at: 'welcome'|'preflight'|'prerequisites'|'protocols'|'vpn'|'data-location'|'credentials'|'provider'|'service-user'|'library'|'household'|'notifications'|'autostart'|'review', offered: bool, phase: 'in-progress'|'reviewing'|'applying'|'applied', plan: list<array{key: string, secret: bool, value: string}>, proof?: array{observed: string, outcome: 'valid'}|array{detail: string, outcome: 'rejected'}|array{detail: string, outcome: 'unreachable'}|array{detail: string, outcome: 'degraded'}|null, ready_for_review: bool, unanswered: list<'welcome'|'preflight'|'prerequisites'|'protocols'|'vpn'|'data-location'|'credentials'|'provider'|'service-user'|'library'|'household'|'notifications'|'autostart'|'review'>, written: list<string>}
 */
final class WizardEnvelope
{
    /**
     * The kind an envelope must carry to be read as this one.
     */
    public const Kind KIND = Kind::Wizard;

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
