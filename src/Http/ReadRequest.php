<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Http;

use function http_build_query;
use function implode;
use function in_array;
use function is_array;
use function is_bool;

use Override;

use function parse_url;

use const PHP_URL_QUERY;

use Psr\Http\Message\RequestInterface;
use Saloon\Enums\Method;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Http\PendingRequest;
use Saloon\Http\Request;

/**
 * A request that reads, mirroring a command's machine-readable output.
 *
 * A parameter given a list is the same key repeated once for each value, in
 * order (`form=a&form=b`), which is how a command's flag given more than once
 * is written as a read. An empty list sends nothing, as a null does. A
 * boolean is written `true` or `false`, the two words a stack reads as a choice.
 *
 * **A read is asked again before anything is reported**, twice, a little later
 * each time (the pause is the connector's), where nothing answered it or a gateway in front of the stack said
 * it could not reach it. Those are the failures a moment can clear: a phone
 * waking its radio, a proxy whose upstream is restarting. A read changes
 * nothing, so asking it again costs only the wait. Every other answer is the
 * stack's own and comes back on the first attempt: asking again would only
 * repeat it. An action is a different request: it is sent once (see
 * {@see ActionRequest}).
 */
final class ReadRequest extends Request
{
    /** How many times a read is attempted in all: once, and twice more. */
    private const int ATTEMPTS = 3;

    /** What a gateway in front of the stack answers when it could not reach it, or is waiting for it. */
    private const array PASSED_ON = [502, 503, 504];

    #[Override]
    public ?int $tries = self::ATTEMPTS;

    #[Override]
    public ?bool $useExponentialBackoff = true;

    /**
     * The last answer is handed back rather than raised, so a stack's own refusal
     * reaches the caller the way it did before reads were retried.
     */
    #[Override]
    public ?bool $throwOnMaxTries = false;

    #[Override]
    protected Method $method = Method::GET;

    /**
     * @param array<string, scalar|list<scalar>|null> $parameters
     */
    public function __construct(
        private readonly string $endpoint,
        private readonly array $parameters = [],
    ) {}

    public function resolveEndpoint(): string
    {
        return $this->endpoint;
    }

    /**
     * The request with its query written as it is sent: the endpoint's own query
     * as written, then one pair for each value of each parameter.
     */
    #[Override]
    public function handlePsrRequest(RequestInterface $request, PendingRequest $pendingRequest): RequestInterface
    {
        $own = (string) parse_url($this->endpoint, PHP_URL_QUERY);
        $pairs = $own === '' ? [] : [$own];

        foreach ($this->parameters as $key => $value) {
            foreach (is_array($value) ? $value : [$value] as $one) {
                $pair = http_build_query([$key => $this->written($one)]);
                if ($pair !== '') {
                    $pairs[] = $pair;
                }
            }
        }

        return $request->withUri($request->getUri()->withQuery(implode('&', $pairs)));
    }

    /**
     * Whether this failure is one a moment can clear: nothing answered, or a
     * gateway could not reach the stack.
     */
    #[Override]
    public function handleRetry(FatalRequestException|RequestException $exception, Request $request): bool
    {
        return $exception instanceof FatalRequestException
            || in_array($exception->getResponse()->status(), self::PASSED_ON, true);
    }

    /**
     * @return array<string, scalar|list<scalar>|null>
     */
    protected function defaultQuery(): array
    {
        return $this->parameters;
    }

    /**
     * A value as a stack reads it, a boolean as its word rather than PHP's `1` or `0`.
     *
     * @param scalar|null $value
     */
    private function written(mixed $value): int|float|string|null
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        return $value;
    }
}
