<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Http;

use Lemonfiber\Sdk\Admission;
use Override;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

/**
 * The one request that carries a password rather than a token.
 *
 * Separate from {@see ActionRequest}, which is the shape every *other* command
 * takes, because this one is not a command: it is the exchange that produces
 * the credential every command afterwards carries. Sharing the shape would put
 * a password one argument away from every endpoint on the surface.
 *
 * The endpoint is named here rather than taken from a caller for the same
 * reason {@see \Lemonfiber\Sdk\Contract\Api::EVENTS_ENDPOINT} is: a caller that
 * may choose where to send a password is a caller that may send it somewhere
 * else.
 */
final class AdmissionRequest extends Request implements HasBody
{
    use HasJsonBody;

    #[Override]
    protected Method $method = Method::POST;

    public function __construct(private readonly string $password) {}

    public function resolveEndpoint(): string
    {
        return Admission::ENDPOINT;
    }

    /**
     * @return array<string, string>
     */
    protected function defaultBody(): array
    {
        return ['password' => $this->password];
    }
}
