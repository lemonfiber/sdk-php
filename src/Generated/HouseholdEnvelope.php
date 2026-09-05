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
 * The `household` envelope, shaped as the contract describes it.
 *
 * @phpstan-type Data array{available: bool, findings: list<string>, members: list<array{access: array{administrator: bool, age_limit?: int|null, disabled: bool, every_library: bool, libraries: list<string>}, claimed: bool, last_seen?: string|null, name: string, requests: list<array{media?: string|null, state?: 'waiting-for-approval'|'declined'|'failed'|'getting'|'partly-here'|'here'|'gone'|null, title?: string|null}>}>}
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
