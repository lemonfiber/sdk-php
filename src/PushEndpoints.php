<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Exception\ApiVersionMismatch;
use Lemonfiber\Sdk\Exception\RequestFailed;
use Lemonfiber\Sdk\Exception\UnexpectedKind;
use Lemonfiber\Sdk\Exception\Unreachable;
use Lemonfiber\Sdk\Exception\UnreadableResponse;
use Lemonfiber\Sdk\Generated\PushEndpointEnvelope;
use Lemonfiber\Sdk\Generated\PushEndpointsEnvelope;
use Lemonfiber\Sdk\Http\LemonfiberConnector;
use Lemonfiber\Sdk\Http\PushEndpointRequest;

/**
 * The phones that asked one running lemonfiber to wake them, registered and removed.
 *
 * The operator's alone: a member session and a key of any scope are refused. They are listed
 * by reading {@see Contract\Api::PUSH_ENDPOINTS_ENDPOINT}, which answers the `push-endpoints`
 * envelope.
 *
 * @phpstan-import-type Data from PushEndpointEnvelope as Registered
 * @phpstan-import-type Data from PushEndpointsEnvelope as Listing
 */
final readonly class PushEndpoints
{
    public function __construct(private LemonfiberConnector $connector) {}

    /**
     * Register a phone's push endpoint, so the stack wakes it on every alert's onset and
     * resolution.
     *
     * The answer is the endpoint as registered. A name that cannot name one, or an address
     * that is not `https`, or keys that cannot be encrypted to, is {@see Exception\Misasked}
     * with its `PUSH-` code; a name another holds is {@see Exception\Busy}. Sent once.
     *
     * @return Envelope<Registered>
     *
     * @throws ApiVersionMismatch
     * @throws RequestFailed
     * @throws UnexpectedKind
     * @throws Unreachable
     * @throws UnreadableResponse
     */
    public function register(PushEndpointRegistering $registering): Envelope
    {
        return PushEndpointEnvelope::in($this->connector->answer(PushEndpointRequest::registering($registering)));
    }

    /**
     * Remove the push endpoint `$name`, so the stack wakes that phone no more.
     *
     * The answer is every push endpoint as the stack lists them once it is removed, naming the
     * one removed. A name no endpoint holds is {@see Exception\Missing}. Sent once.
     *
     * @return Envelope<Listing>
     *
     * @throws ApiVersionMismatch
     * @throws RequestFailed
     * @throws UnexpectedKind
     * @throws Unreachable
     * @throws UnreadableResponse
     */
    public function remove(string $name): Envelope
    {
        return PushEndpointsEnvelope::in($this->connector->answer(PushEndpointRequest::removing($name)));
    }
}
