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
 * A request that lets go of a name and leaves the work it stands for running.
 *
 * Beside {@see ReleaseRequest}, which ends the work. This only takes the name
 * away: what it answers with is where the work stood, the same answer asking
 * about the name would have given, and from then on the name is one this run
 * never issued.
 *
 * @implements ReadsItsAnswer<JobStanding>
 */
final class ForgetRequest extends Request implements ReadsItsAnswer
{
    #[Override]
    protected Method $method = Method::POST;

    public function __construct(private readonly string $job) {}

    public function resolveEndpoint(): string
    {
        return Api::forgotten($this->job);
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
