<?php

// Generated from contract/web-api.contract.json. Do not edit.
// Source: 387f0c83c55614ac3839bb21e88dd0c3a2feb50f, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\Payload;
use Lemonfiber\Sdk\Exception\UnexpectedKind;

/**
 * The `backup` envelope, shaped as the contract describes it.
 *
 * @phpstan-type Data array{path: string, pruned: list<string>, scope: array{scope: 'whole_stack'}|array{name: string, scope: 'service'}|array{project: string, scope: 'existing', trees: list<array{archive_path: string, host_path: string}>}, sensitive: bool}
 */
final class BackupEnvelope
{
    /**
     * The kind an envelope must carry to be read as this one.
     */
    public const Kind KIND = Kind::Backup;

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
