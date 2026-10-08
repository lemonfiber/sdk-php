<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Http;

use Lemonfiber\Sdk\Admission;
use Lemonfiber\Sdk\Admitted;
use Lemonfiber\Sdk\Envelope\EnvelopeReader;
use Lemonfiber\Sdk\Exception\ApiVersionMismatch;
use Lemonfiber\Sdk\Exception\UnexpectedKind;
use Lemonfiber\Sdk\Exception\UnreadableResponse;
use Lemonfiber\Sdk\Generated\AdmissionEnvelope;
use Override;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;

/**
 * The one request that carries a password rather than a token.
 *
 * Separate from {@see ActRequest}, which is the shape every *other* command
 * takes, because this one is not a command: it is the exchange that produces
 * the credential every command afterwards carries. Sharing the shape would put
 * a password one argument away from every endpoint on the surface.
 *
 * The endpoint is named here rather than taken from a caller for the same
 * reason {@see \Lemonfiber\Sdk\Contract\Api::EVENTS_ENDPOINT} is: a caller that
 * may choose where to send a password is a caller that may send it somewhere
 * else.
 *
 * @implements ReadsItsAnswer<Admitted>
 */
final class AdmissionRequest extends Request implements HasBody, ReadsItsAnswer
{
    use HasJsonBody;

    #[Override]
    protected Method $method = Method::POST;

    /**
     * @param string|null $name who the caller says they are, which a household member gives and the operator does not
     */
    public function __construct(
        private readonly string $password,
        private readonly ?string $name = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return Admission::ENDPOINT;
    }

    /**
     * The credential the door handed over, and who it names.
     *
     * @throws ApiVersionMismatch
     * @throws UnexpectedKind
     * @throws UnreadableResponse
     */
    #[Override]
    public function createDtoFromResponse(Response $response): Admitted
    {
        /** @var array{member?: string|null, token: string, until: string} $data */
        $data = AdmissionEnvelope::in(new EnvelopeReader()->read($response->body()))->data;

        // Absent and present-and-null are one answer on this field, which is
        // what the schema says of it: optional there, nullable in the type, and
        // either way of leaving it out names the operator. `??` reads both
        // without asking which of the two a stack chose to send.
        return Admitted::of($data['token'], $data['until'], $data['member'] ?? null);
    }

    /**
     * @return array<string, string>
     */
    protected function defaultBody(): array
    {
        return $this->name === null
            ? ['password' => $this->password]
            : ['name' => $this->name, 'password' => $this->password];
    }
}
