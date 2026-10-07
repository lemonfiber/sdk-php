<?php

// Generated from contract/web-api.contract.json. Do not edit.
// Source: 5a9496cec5089736f1f018ae55c551dd30cd6673, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\Payload;
use Lemonfiber\Sdk\Exception\UnexpectedKind;

/**
 * The `wizard` envelope, shaped as the contract describes it.
 *
 * @phpstan-import-type SettingReport from Shapes
 * @phpstan-import-type Validation from Shapes
 * @phpstan-type Phase 'in-progress'|'reviewing'|'applying'|'applied'
 * @phpstan-type Ran bool
 * @phpstan-type WizardReport array{asks: bool, at: WizardStep, offered: bool, phase: Phase, plan: list<SettingReport>, proof?: Validation|null, ready_for_review: bool, rehearsed: Ran, unanswered: list<WizardStep>, written: list<string>}
 * @phpstan-type WizardStep 'welcome'|'preflight'|'prerequisites'|'protocols'|'vpn'|'data-location'|'credentials'|'provider'|'service-user'|'library'|'household'|'notifications'|'autostart'|'review'
 * @phpstan-type Data WizardReport
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

        return new Envelope($envelope->apiVersion, $envelope->kind, $data, $envelope->host);
    }
}
