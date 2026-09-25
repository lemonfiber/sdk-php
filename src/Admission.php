<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk;

use function is_numeric;
use function is_string;

use Lemonfiber\Sdk\Envelope\EnvelopeReader;
use Lemonfiber\Sdk\Exception\ApiVersionMismatch;
use Lemonfiber\Sdk\Exception\ConfigurationProblem;
use Lemonfiber\Sdk\Exception\PasswordWasRefused;
use Lemonfiber\Sdk\Exception\RequestFailed;
use Lemonfiber\Sdk\Exception\TooManyAttempts;
use Lemonfiber\Sdk\Exception\Unreachable;
use Lemonfiber\Sdk\Exception\UnreadableResponse;
use Lemonfiber\Sdk\Generated\AdmissionEnvelope;
use Lemonfiber\Sdk\Http\AdmissionRequest;
use Lemonfiber\Sdk\Http\BaseUrl;
use Lemonfiber\Sdk\Http\CertificatePin;
use Lemonfiber\Sdk\Http\LemonfiberConnector;

/**
 * The door: a password exchanged, once, for a session.
 *
 * A type of its own rather than a method on {@see Client}, because it is not
 * one. Every other call on this surface carries a token and this is what
 * produces one, so a client that could also knock would be a client holding a
 * password. Verifying a password is deliberately expensive, and one re-sent on
 * every request is one with more chances to leak — so it is exchanged once, and
 * the type that does the exchanging is not the type that lives afterwards.
 *
 * Its shape follows the server's: `/api/session` is the only route that answers
 * a request carrying no token, and the three ways it can answer are three
 * different things to whoever is holding the phone.
 *
 * | answer | what it means | what it raises |
 * |---|---|---|
 * | `200` | admitted | nothing; a {@see Admitted} comes back |
 * | `401` | that is not the password | {@see PasswordWasRefused} |
 * | `429` | too many wrong ones, wait | {@see TooManyAttempts} |
 *
 * The first two are the distinction the surface goes out of its way to make —
 * every other refusal there answers `403`, which means *nothing you could send
 * would help*. Flattening them into one failure would leave a caller unable to
 * tell whether offering a login is worth anything, which is the whole reason
 * the server answers two codes.
 */
final readonly class Admission
{
    /**
     * Where a password is exchanged for a session.
     *
     * Written here rather than taken from a caller, for the reason
     * {@see Contract\Api::EVENTS_ENDPOINT} is: a caller that
     * may choose where to send a password is a caller that may send it
     * somewhere else.
     */
    public const string ENDPOINT = '/api/session';

    /** What the answer says, in its own header, when it will listen again. */
    private const string RETRY_AFTER = 'Retry-After';

    private const int NOT_THE_PASSWORD = 401;

    private const int TOO_MANY = 429;

    private function __construct(
        private LemonfiberConnector $connector,
        private EnvelopeReader $reader,
    ) {}

    /**
     * The door of a stack reached over the network, held to its certificate.
     *
     * Pinned, and there is no unpinned counterpart on purpose: this is the one
     * request that carries the operator's password, so it is the last request
     * that should ever reach a machine whose identity nothing established.
     *
     * @throws ConfigurationProblem
     */
    public static function at(string $address, string $certificateDigest): self
    {
        return new self(
            new LemonfiberConnector(BaseUrl::pinned($address, CertificatePin::fromSha256($certificateDigest))),
            new EnvelopeReader(),
        );
    }

    /**
     * The door of the machine this process is running on.
     *
     * No pin, because there is nothing between here and a loopback port for a
     * pin to protect against — which is the same argument
     * {@see BaseUrl::onPort()} already makes, and why it refuses one.
     *
     * @throws ConfigurationProblem
     */
    public static function onPort(int $port): self
    {
        return new self(new LemonfiberConnector(BaseUrl::onPort($port)), new EnvelopeReader());
    }

    /**
     * Offer a password, and come away with a session or with a reason.
     *
     * @throws ApiVersionMismatch
     * @throws PasswordWasRefused
     * @throws RequestFailed
     * @throws TooManyAttempts
     * @throws Unreachable
     * @throws UnreadableResponse
     */
    public function open(string $password): Admitted
    {
        $response = $this->connector->send(new AdmissionRequest($password));
        $status = $response->status();

        if ($status === self::NOT_THE_PASSWORD) {
            throw PasswordWasRefused::atTheDoor();
        }

        if ($status === self::TOO_MANY) {
            /** @var array<mixed>|string|null $said */
            $said = $response->header(self::RETRY_AFTER);

            throw $this->waitOf(is_string($said) ? $said : null);
        }

        if ($response->failed()) {
            throw RequestFailed::from(self::ENDPOINT, $status, $response->body());
        }

        return $this->admitted($this->reader->read($response->body()));
    }

    /**
     * The transport underneath this door.
     *
     * The same accessor {@see Client::connector()} publishes and for the same
     * reason: a caller substituting the transport — a test, or a consumer
     * wiring its own middleware — has one place to reach rather than a
     * constructor that takes one and a password in the same breath.
     */
    public function connector(): LemonfiberConnector
    {
        return $this->connector;
    }

    /**
     * The wait the answer named, or that it named none.
     *
     * `Retry-After` may also carry a date rather than a count of seconds, and
     * this reads only the count — a date read wrong is a countdown that ends
     * before the door opens, which is worse than saying nothing. The server
     * sends seconds.
     *
     * A header may arrive repeated, which the client hands over as a list. That
     * is not a wait this can read either, and it takes the same road as an
     * absent one: {@see TooManyAttempts::forAWhile()} says the door is shut and
     * declines to guess for how long.
     */
    private function waitOf(?string $said): TooManyAttempts
    {
        // No null guard beside this: `is_numeric(null)` is already false, so
        // one would be a branch that cannot be told from its absence — an
        // absent header and an unreadable one both mean the same thing here,
        // and that is the answer either way.
        return is_numeric($said)
            ? TooManyAttempts::forAnother((int) $said)
            : TooManyAttempts::forAWhile();
    }

    /**
     * The envelope, read as what came back.
     *
     * @param Envelope\Envelope<mixed> $envelope
     *
     * @throws UnreadableResponse
     */
    private function admitted(object $envelope): Admitted
    {
        /** @var array{member?: string|null, token: string, until: string} $data */
        $data = AdmissionEnvelope::in($envelope)->data;

        // Absent and present-and-null are one answer on this field, which is
        // what the schema says of it: optional there, nullable in the type, and
        // either way of leaving it out names the operator. `??` reads both
        // without asking which of the two a stack chose to send.
        return Admitted::of($data['token'], $data['until'], $data['member'] ?? null);
    }
}
