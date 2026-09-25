<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Http;

use function http_build_query;
use function implode;
use function is_array;

use Override;

use function parse_url;

use const PHP_URL_QUERY;

use Psr\Http\Message\RequestInterface;
use Saloon\Enums\Method;
use Saloon\Http\PendingRequest;
use Saloon\Http\Request;

/**
 * A request that reads, mirroring a command's machine-readable output.
 *
 * A parameter given a list is the same key repeated once for each value, in
 * order (`form=a&form=b`), which is how a command's flag given more than once
 * is written as a read. An empty list sends nothing, as a null does.
 */
final class ReadRequest extends Request
{
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
                $pair = http_build_query([$key => $one]);
                if ($pair !== '') {
                    $pairs[] = $pair;
                }
            }
        }

        return $request->withUri($request->getUri()->withQuery(implode('&', $pairs)));
    }

    /**
     * @return array<string, scalar|list<scalar>|null>
     */
    protected function defaultQuery(): array
    {
        return $this->parameters;
    }
}
