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
 * The `restore` envelope, shaped as the contract describes it.
 *
 * @phpstan-type Data array{done?: array{from_version: string, relocated?: array{now: string, was: string}|null, scope: array{scope: 'whole_stack'}|array{name: string, scope: 'service'}}|null, would: array{agreement: string, downgrade: bool, manifest: array{created_at: string, data_root: string, members: list<array{archive_path: string, label: string}>, product_version: string, schema: int, scope: array{scope: 'whole_stack'}|array{name: string, scope: 'service'}, sensitive: bool}, relocation?: array{now: string, was: string}|null}}
 */
final class RestoreEnvelope
{
    /**
     * The kind an envelope must carry to be read as this one.
     */
    public const Kind KIND = Kind::Restore;

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
