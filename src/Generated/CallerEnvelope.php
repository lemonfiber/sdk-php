<?php

// Generated from the contract vendored under contract/. Do not edit.
// Contract::SOURCE names its revision and Contract::API_VERSION its wire version.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\Payload;
use Lemonfiber\Sdk\Exception\UnexpectedKind;

/**
 * The `caller` envelope, shaped as the contract describes it.
 *
 * @phpstan-type Caller array{as: 'operator'}|array{as: 'member', id: string}|array{as: 'key', name: string, scope: string}
 * @phpstan-type Calling array{caller: Caller, standing: 'known'}|array{standing: 'nobody'}|array{standing: 'unconfirmed'}|array{retry_after: int, standing: 'held'}|array{standing: 'exposed'}
 * @phpstan-type Data Calling
 */
final class CallerEnvelope
{
    /**
     * The kind an envelope must carry to be read as this one.
     */
    public const Kind KIND = Kind::Caller;

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

        return new Envelope($envelope->apiVersion, $envelope->kind, $data, $envelope->host, $envelope->job);
    }
}
