<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Http;

use Lemonfiber\Sdk\Contract\Api;
use Lemonfiber\Sdk\JobStanding;
use Override;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

/**
 * A request that lets go of a name, ending the work it stands for.
 *
 * Separate from {@see ActionRequest}, which asks for work to be done. This asks
 * for work already begun to stop, and what it answers with is where that work
 * got to — the same answer asking about the name would have given, so a caller
 * that released one need not ask again to find out what it released.
 *
 * @implements ReadsItsAnswer<JobStanding>
 */
final class ReleaseRequest extends Request implements ReadsItsAnswer
{
    #[Override]
    protected Method $method = Method::DELETE;

    public function __construct(private readonly string $job) {}

    public function resolveEndpoint(): string
    {
        return Api::job($this->job);
    }

    #[Override]
    public function createDtoFromResponse(Response $response): JobStanding
    {
        return JobAnswer::standing($this->job, $response);
    }

    #[Override]
    public function hasRequestFailed(Response $response): bool
    {
        return JobAnswer::refused($response);
    }
}
