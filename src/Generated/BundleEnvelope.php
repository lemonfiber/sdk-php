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
 * The `bundle` envelope, shaped as the contract describes it.
 *
 * @phpstan-type Bundle array{bytes: int, contents: Contents, path?: string|null, rehearsed: bool, would_go?: string|null}
 * @phpstan-type Contents array{missing: list<string>, pieces: list<Piece>, taken: Taken, terms: Terms}
 * @phpstan-type Filenames bool
 * @phpstan-type Piece array{body: string, name: string}
 * @phpstan-type Taken array{at: string, lemonfiber: string, stack: string}
 * @phpstan-type Terms array{filenames: Filenames, revealed: list<string>, window: string}
 * @phpstan-type Data Bundle
 */
final class BundleEnvelope
{
    /**
     * The kind an envelope must carry to be read as this one.
     */
    public const Kind KIND = Kind::Bundle;

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
