<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Http;

use Override;
use Saloon\Enums\Method;
use Saloon\Http\Request;

/**
 * A request that lets go of a name, ending the work it stands for.
 *
 * Separate from {@see ActionRequest}, which asks for work to be done. This asks
 * for work already begun to stop, and what it answers with is where that work
 * got to — the same answer asking about the name would have given, so a caller
 * that released one need not ask again to find out what it released.
 */
final class ReleaseRequest extends Request
{
    #[Override]
    protected Method $method = Method::DELETE;

    public function __construct(private readonly string $endpoint) {}

    public function resolveEndpoint(): string
    {
        return $this->endpoint;
    }
}
