<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: a1ca5f05d410c09480bf901a4c442e5b141aa1b7, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\Payload;
use Lemonfiber\Sdk\Exception\UnexpectedKind;

/**
 * The `restore` envelope, shaped as the contract describes it.
 *
 * @phpstan-import-type Scope from Shapes
 * @phpstan-type BackupManifest array{created_at: string, data_root: string, members: list<Member>, product_version: string, schema: int, scope: Scope, sensitive: bool}
 * @phpstan-type Member array{archive_path: string, label: string}
 * @phpstan-type Preview array{agreement: string, downgrade: bool, manifest: BackupManifest, relocation?: Relocation|null}
 * @phpstan-type Relocation array{now: string, was: string}
 * @phpstan-type Restoration array{done?: RestoreReport|null, rehearsed: bool, would: Preview}
 * @phpstan-type RestoreReport array{from_version: string, relocated?: Relocation|null, scope: Scope}
 * @phpstan-type Data Restoration
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

        return new Envelope($envelope->apiVersion, $envelope->kind, $data, $envelope->host);
    }
}
