<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Exception\ApiVersionMismatch;
use Lemonfiber\Sdk\Exception\RequestFailed;
use Lemonfiber\Sdk\Exception\Unreachable;
use Lemonfiber\Sdk\Exception\UnreadableResponse;
use Lemonfiber\Sdk\Http\DeviceKeyRequest;
use Lemonfiber\Sdk\Http\LemonfiberConnector;
use Lemonfiber\Sdk\Http\MintKeyRequest;
use Lemonfiber\Sdk\Http\RevokeKeyRequest;
use SensitiveParameter;

/**
 * The operator's integration keys and the device keys that may prove a mint, on one running lemonfiber.
 */
final readonly class Keys
{
    public function __construct(private LemonfiberConnector $connector) {}

    /**
     * Revoke the integration key of that name.
     *
     * The answer is the `keys` envelope as the stack lists them once the key is
     * revoked ({@see Generated\KeysEnvelope}). A name no key holds, or one
     * that is not the caller's to revoke, is {@see Exception\Missing}. Sent once.
     *
     * @return Envelope<mixed>
     *
     * @throws ApiVersionMismatch
     * @throws RequestFailed
     * @throws Unreachable
     * @throws UnreadableResponse
     */
    public function revoke(string $name): Envelope
    {
        return $this->connector->answer(new RevokeKeyRequest($name));
    }

    /**
     * Mint an integration key, proved by the operator's password or by a device key's
     * signature over a challenge from {@see self::deviceChallenge()}.
     *
     * The answer is the `minted-key` envelope carrying the secret, once
     * ({@see Generated\MintedKeyEnvelope}), or for a claim the `key-claim` envelope
     * ({@see Generated\KeyClaimEnvelope}). A wrong password or signature counts against the
     * limit wrong passwords meet. Sent once.
     *
     * @return Envelope<mixed>
     *
     * @throws ApiVersionMismatch
     * @throws RequestFailed
     * @throws Unreachable
     * @throws UnreadableResponse
     */
    public function mint(KeyMint $mint, MintProof $proof): Envelope
    {
        return $this->connector->answer(new MintKeyRequest($mint, $proof));
    }

    /**
     * Register a phone's key pair as the device key `$name`: its public half as its
     * `SubjectPublicKeyInfo` DER, the platform's attestation, and the operator's password,
     * given once and never kept.
     *
     * The answer is the `device-key` envelope ({@see Generated\DeviceKeyEnvelope}). Each
     * reason a registration is refused carries a `DEVICE-` code of its own. Sent once.
     *
     * @return Envelope<mixed>
     *
     * @throws ApiVersionMismatch
     * @throws RequestFailed
     * @throws Unreachable
     * @throws UnreadableResponse
     */
    public function registerDevice(
        string $name,
        string $publicKey,
        DeviceAttestation $attestation,
        #[SensitiveParameter]
        string $password,
    ): Envelope {
        return $this->connector->answer(DeviceKeyRequest::registering($name, $publicKey, $attestation, $password));
    }

    /**
     * A challenge for the device key `$name`, good for one mint from this client's token
     * within a minute.
     *
     * The answer is the `device-challenge` envelope ({@see Generated\DeviceChallengeEnvelope}).
     * Asking again spends the challenge asked before. Sent once.
     *
     * @return Envelope<mixed>
     *
     * @throws ApiVersionMismatch
     * @throws RequestFailed
     * @throws Unreachable
     * @throws UnreadableResponse
     */
    public function deviceChallenge(string $name): Envelope
    {
        return $this->connector->answer(DeviceKeyRequest::challenging($name));
    }

    /**
     * Revoke the device key `$name`, spending every challenge standing for it. The keys it
     * proved stay.
     *
     * The answer is the `device-keys` envelope as the stack lists them once it is revoked
     * ({@see Generated\DeviceKeysEnvelope}). Sent once.
     *
     * @return Envelope<mixed>
     *
     * @throws ApiVersionMismatch
     * @throws RequestFailed
     * @throws Unreachable
     * @throws UnreadableResponse
     */
    public function revokeDevice(string $name): Envelope
    {
        return $this->connector->answer(DeviceKeyRequest::revoking($name));
    }
}
