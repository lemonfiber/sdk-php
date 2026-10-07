<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Http;

use Lemonfiber\Sdk\Exception\Problem;
use Saloon\Http\Response;

/**
 * A request that knows what its own answer is.
 *
 * Saloon's `createDtoFromResponse`, typed: each request reads the answer it was
 * sent for, so how one is read sits beside how it is asked for, and the client
 * only sends. It is reached once the answer is known not to be a refusal.
 *
 * @template-covariant T
 */
interface ReadsItsAnswer
{
    /**
     * @return T
     *
     * @throws Problem where the answer cannot be read as what was asked for
     */
    public function createDtoFromResponse(Response $response): mixed;
}
