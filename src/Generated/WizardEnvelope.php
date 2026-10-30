<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: b81064077ef45a4fdc104931ecace46d1e85f55f, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\Payload;
use Lemonfiber\Sdk\Exception\UnexpectedKind;

/**
 * The `wizard` envelope, shaped as the contract describes it.
 *
 * @phpstan-import-type Choosable from Shapes
 * @phpstan-import-type SettingReport from Shapes
 * @phpstan-import-type Validation from Shapes
 * @phpstan-type GuardQuestion array{asks: 'choice', choices: list<Choosable>, default?: string|null, setting: string}|array{asks: 'value', needs: array<string, list<string>>, setting: string}|array{asks: 'secret', needs: array<string, list<string>>, secret: string, why: string}
 * @phpstan-type Phase 'in-progress'|'reviewing'|'applying'|'applied'
 * @phpstan-type Ran bool
 * @phpstan-type WizardReport array{asks: bool, at: WizardStep, guard: list<GuardQuestion>, offered: bool, phase: Phase, plan: list<SettingReport>, proof?: Validation|null, ready_for_review: bool, rehearsed: Ran, unanswered: list<WizardStep>, written: list<string>}
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
