<?php

// Generated from contract/web-api.contract.json. Do not edit.
// Source: 23e2c42506e8210fb26c35c56448b098b185ebd6, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\Payload;
use Lemonfiber\Sdk\Exception\UnexpectedKind;

/**
 * The `household` envelope, shaped as the contract describes it.
 *
 * @phpstan-type Data array{allows?: string|null, available: bool, filtering?: string|null, findings: list<string>, members: list<array{access: array{administrator: bool, age_limit?: int|null, disabled: bool, every_library: bool, libraries: list<string>, rated?: array{allows: list<string>, fell_back: bool, holds_back: list<string>}|null, restriction: 'unrestricted'|'rating-limited'|'library-limited'|'both'|'inconsistent', unrated: 'held-back'|'let-through'}, asking?: array{films: array{limit?: int|null, period?: string|null, remaining?: int|null, used: int}, frees_up?: string|null, policy: 'trusted'|'within-a-limit'|'everything-waits', standing: 'unlimited'|'within-quota'|'near-quota'|'quota-exhausted', television: array{limit?: int|null, period?: string|null, remaining?: int|null, used: int}}|null, claimed: bool, last_seen?: string|null, name: string, requests: list<array{estimate?: array{bytes: int, measured: bool}|null, id: int, media?: string|null, refused?: array{at?: string|null, expired?: bool, reason: string, told?: array{at?: string|null, to: list<string>}|null}|null, state?: 'waiting-for-approval'|'declined'|'failed'|'getting'|'partly-here'|'here'|'gone'|null, title?: string|null, waiting_days?: int|null}>, to_hand_over: list<string>}>, policy?: 'trusted'|'within-a-limit'|'everything-waits'|null}
 */
final class HouseholdEnvelope
{
    /**
     * The kind an envelope must carry to be read as this one.
     */
    public const Kind KIND = Kind::Household;

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
