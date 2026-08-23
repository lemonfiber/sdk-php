<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Http;

use Lemonfiber\Sdk\Contract\Api;
use Lemonfiber\Sdk\Exception\ConfigurationProblem;

use function preg_match;

use Saloon\Contracts\Authenticator;
use Saloon\Http\PendingRequest;

use function trim;

/**
 * The per-run token lemonfiber prints at start, travelling as a header.
 */
final readonly class RunToken implements Authenticator
{
    private const string HIDDEN = '(hidden)';

    private const string FORBIDDEN_CHARACTERS = '/[\x00-\x1F\x7F]/';

    private function __construct(private string $value) {}

    /**
     * @return array<string, string>
     */
    public function __debugInfo(): array
    {
        return ['value' => self::HIDDEN];
    }

    /**
     * @throws ConfigurationProblem
     */
    public static function fromString(string $token): self
    {
        if (trim($token) === '') {
            throw ConfigurationProblem::tokenIsEmpty();
        }

        if (preg_match(self::FORBIDDEN_CHARACTERS, $token) === 1) {
            throw ConfigurationProblem::tokenHasHiddenCharacters();
        }

        return new self($token);
    }

    public function set(PendingRequest $pendingRequest): void
    {
        $pendingRequest->headers()->add(Api::TOKEN_HEADER, $this->value);
    }
}
