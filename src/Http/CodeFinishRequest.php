<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Http;

use function json_encode;

use const JSON_THROW_ON_ERROR;

use Lemonfiber\Sdk\Admission;
use Lemonfiber\Sdk\Admitted;
use Lemonfiber\Sdk\CodeStillWaiting;
use Lemonfiber\Sdk\Envelope\EnvelopeReader;
use Lemonfiber\Sdk\Exception\ApiVersionMismatch;
use Lemonfiber\Sdk\Exception\UnexpectedKind;
use Lemonfiber\Sdk\Exception\UnreadableResponse;
use Lemonfiber\Sdk\Generated\AdmissionEnvelope;
use Lemonfiber\Sdk\Generated\Kind;
use Lemonfiber\Sdk\Generated\SignInCodeEnvelope;
use Override;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasStringBody;

/**
 * Finishing the sign-in a code began, by the handle the door answered with it.
 *
 * @implements ReadsItsAnswer<Admitted|CodeStillWaiting>
 */
final class CodeFinishRequest extends Request implements HasBody, ReadsItsAnswer
{
    use HasStringBody;

    #[Override]
    protected Method $method = Method::POST;

    public function __construct(private readonly string $waiting) {}

    /**
     * The member's session once the code was approved, or that it still waits.
     *
     * @throws ApiVersionMismatch
     * @throws UnexpectedKind
     * @throws UnreadableResponse
     */
    #[Override]
    public function createDtoFromResponse(Response $response): Admitted|CodeStillWaiting
    {
        $envelope = new EnvelopeReader()->read($response->body());

        if ($envelope->kind === Kind::Admission->value) {
            $data = AdmissionEnvelope::in($envelope)->data;

            return Admitted::of($data['token'], $data['until'], $data['member'] ?? null);
        }

        $data = SignInCodeEnvelope::in($envelope)->data;

        return new CodeStillWaiting($data['waiting'], $data['lapses_at']);
    }

    public function resolveEndpoint(): string
    {
        return Admission::CODE_ENDPOINT;
    }

    protected function defaultBody(): string
    {
        return json_encode(['waiting' => $this->waiting], JSON_THROW_ON_ERROR);
    }

    /** @return array<string, string> */
    protected function defaultHeaders(): array
    {
        return ['Content-Type' => 'application/json'];
    }
}
