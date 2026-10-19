<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: 675430a7b93f8a4abbd859b5fe7702f836bd3207, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\Payload;
use Lemonfiber\Sdk\Exception\UnexpectedKind;

/**
 * The `device-keys` envelope, shaped as the contract describes it.
 *
 * @phpstan-import-type DeviceKeyBacking from Shapes
 * @phpstan-type DeviceKeyListing array{keys: list<ListedDeviceKey>, rehearsed: bool, revoked?: string|null}
 * @phpstan-type ListedDeviceKey array{app: string, backing: DeviceKeyBacking, fingerprint: string, name: string, registered: string, revoked?: string|null, used?: string|null}
 * @phpstan-type Data DeviceKeyListing
 */
final class DeviceKeysEnvelope
{
    /**
     * The kind an envelope must carry to be read as this one.
     */
    public const Kind KIND = Kind::DeviceKeys;

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
