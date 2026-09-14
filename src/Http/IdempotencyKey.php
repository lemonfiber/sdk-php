<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Http;

use Lemonfiber\Sdk\Contract\Api;
use Lemonfiber\Sdk\Exception\ConfigurationProblem;

use function preg_match;
use function trim;

/**
 * The name one attempt at an action travels under, as a header.
 *
 * A caller that lost the answer to an action re-sends it under the same key
 * and the stack recognises the second send as the first. A caller that has
 * reconnected, or that is acting again, sends a new one.
 *
 * Checked here rather than at the socket, the way {@see RunToken} is: a value
 * carrying a carriage return ends the header and starts another, so a key
 * built out of something a caller was handed is a way to write headers this
 * client never wrote.
 *
 * **Not an authenticator.** {@see RunToken} is set on the connection and
 * travels with everything sent over it, which is right for a credential and
 * wrong for this: a key that outlives one request is a key that names two
 * attempts, and the second of them is a change the operator did not ask for
 * twice. It reaches a single {@see ActionRequest} and goes no further.
 */
final readonly class IdempotencyKey
{
    /**
     * What cannot travel in a header value, a control character being the
     * half of it that ends the header early.
     */
    private const string FORBIDDEN_CHARACTERS = '/[\x00-\x1F\x7F]/';

    private function __construct(private string $value) {}

    /**
     * The one place a string becomes a key.
     *
     * @throws ConfigurationProblem
     */
    public static function fromString(string $key): self
    {
        if (trim($key) === '') {
            throw ConfigurationProblem::attemptIsUnnamed();
        }

        if (preg_match(self::FORBIDDEN_CHARACTERS, $key) === 1) {
            throw ConfigurationProblem::attemptNameHasHiddenCharacters();
        }

        return new self($key);
    }

    /**
     * The header this key travels in, as a request carries it.
     *
     * @return array<string, string>
     */
    public function header(): array
    {
        return [Api::IDEMPOTENCY_HEADER => $this->value];
    }
}
