<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Http;

use function is_string;

use Lemonfiber\Sdk\Admission;
use Lemonfiber\Sdk\CodeIssued;
use Lemonfiber\Sdk\Envelope\EnvelopeReader;
use Lemonfiber\Sdk\Exception\ApiVersionMismatch;
use Lemonfiber\Sdk\Exception\UnexpectedKind;
use Lemonfiber\Sdk\Exception\UnreadableResponse;
use Lemonfiber\Sdk\Generated\SignInCodeEnvelope;
use Lemonfiber\Sdk\WaitingCode;
use Override;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasStringBody;
use SensitiveParameter;

/**
 * Asking the door for a code to sign a member in by.
 *
 * @implements ReadsItsAnswer<CodeIssued>
 */
final class CodeAskRequest extends Request implements HasBody, ReadsItsAnswer
{
    use HasStringBody;

    #[Override]
    protected Method $method = Method::POST;

    /**
     * The code issued, with where it is approved.
     *
     * @throws ApiVersionMismatch
     * @throws UnexpectedKind
     * @throws UnreadableResponse
     */
    #[Override]
    public function createDtoFromResponse(#[SensitiveParameter] Response $response): CodeIssued
    {
        $data = SignInCodeEnvelope::in(new EnvelopeReader()->read($response->body()))->data;
        $code = $data['code'] ?? null;
        $approveAt = $data['approve_at'] ?? null;

        if (! is_string($code) || ! is_string($approveAt)) {
            throw UnreadableResponse::codeUnissued();
        }

        return new CodeIssued($code, $approveAt, $data['lapses_at'], WaitingCode::of($data['waiting']));
    }

    public function resolveEndpoint(): string
    {
        return Admission::CODE_ENDPOINT;
    }

    protected function defaultBody(): string
    {
        return '{}';
    }

    /** @return array<string, string> */
    protected function defaultHeaders(): array
    {
        return ['Content-Type' => 'application/json'];
    }
}
