<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Http;

use Override;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

/**
 * A request that acts, mirroring a command.
 *
 * The key naming the attempt is held per request rather than on the
 * connection. One client sends many actions, and a key set once for all of
 * them would tell a stack that every one of them was a re-send of the first.
 *
 * **Sent once more under its key where nothing answered.** A connection that
 * broke may have carried the action and lost its answer, and the key is what
 * makes sending it again one act rather than two. So an action carrying a key
 * is sent a second time, under the same key, where no answer came back, and
 * only within the wait its call was given. An answer of any kind, a refusal
 * included, is the stack's and comes back as it is. An action carrying no key
 * is never sent again: nothing would tell the stack it was the same act.
 */
final class ActionRequest extends Request implements HasBody
{
    use HasJsonBody;

    /** How many times an action carrying a key is sent in all: once, and once more. */
    private const int ATTEMPTS_UNDER_A_KEY = 2;

    #[Override]
    public ?bool $useExponentialBackoff = true;

    /**
     * The last answer is handed back rather than raised, so a stack's own refusal
     * reaches the caller as it does on an action sent once.
     */
    #[Override]
    public ?bool $throwOnMaxTries = false;

    #[Override]
    protected Method $method = Method::POST;

    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(
        private readonly string $endpoint,
        private readonly array $payload = [],
        private readonly ?IdempotencyKey $attempt = null,
    ) {
        $this->tries = $attempt instanceof IdempotencyKey ? self::ATTEMPTS_UNDER_A_KEY : null;
    }

    /**
     * Whether this failure is one sending again under the same key may clear: nothing answered.
     */
    #[Override]
    public function handleRetry(FatalRequestException|RequestException $exception, Request $request): bool
    {
        return $exception instanceof FatalRequestException;
    }

    public function resolveEndpoint(): string
    {
        return $this->endpoint;
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultBody(): array
    {
        return $this->payload;
    }

    /**
     * The headers this request carries of its own, which is the key or none.
     *
     * @return array<string, string>
     */
    protected function defaultHeaders(): array
    {
        return $this->attempt?->header() ?? [];
    }
}
