<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk;

use function implode;

use Lemonfiber\Sdk\Exception\ConfigurationProblem;

use function str_contains;

/**
 * An integration key to mint: what to call it, what it admits, what it is for, and whether
 * it comes back as a claim.
 *
 * A phone proving the mint with a device key signs {@see self::signedText()} over the
 * challenge the stack issued, and the stack verifies that signature over these same four
 * values, so no part may hold the line feed the text is cut at.
 */
final readonly class KeyMint
{
    /**
     * The first line of the text a device key signs, naming its form.
     */
    public const string FORM = 'lemonfiber-mint/1';

    /**
     * What ends every line of the signed text.
     */
    private const string LINE_END = "\n";

    /**
     * The last line of the signed text for a mint that answers with a claim.
     */
    private const string CLAIM = 'claim';

    /**
     * The last line of the signed text for a mint that answers with the secret.
     */
    private const string KEY = 'key';

    private function __construct(
        public string $name,
        public string $scope,
        public string $purpose,
        public bool $claim,
    ) {}

    /**
     * A key named `$name`, admitting `$scope` (`read`, `act` or `member:<account>`), for
     * `$purpose` (`home-assistant`, `mcp` or `other`), answered with a claim where `$claim`.
     *
     * @throws ConfigurationProblem
     */
    public static function of(string $name, string $scope, string $purpose, bool $claim = false): self
    {
        foreach ([$name, $scope, $purpose] as $part) {
            if (str_contains($part, self::LINE_END)) {
                throw ConfigurationProblem::mintPartHoldsALineFeed();
            }
        }

        return new self($name, $scope, $purpose, $claim);
    }

    /**
     * The text a device key signs to prove this mint over `$challenge`: the form, the
     * challenge, the name, the scope, the purpose and `claim` or `key`, each ended by a line
     * feed, as UTF-8.
     *
     * @throws ConfigurationProblem
     */
    public function signedText(string $challenge): string
    {
        if (str_contains($challenge, self::LINE_END)) {
            throw ConfigurationProblem::mintPartHoldsALineFeed();
        }

        $lines = [self::FORM, $challenge, $this->name, $this->scope, $this->purpose, $this->claim ? self::CLAIM : self::KEY];

        return implode(self::LINE_END, $lines) . self::LINE_END;
    }

    /**
     * The mint's own fields, as the request body carries them.
     *
     * @return array{name: string, scope: string, purpose: string, claim: bool}
     */
    public function fields(): array
    {
        return ['name' => $this->name, 'scope' => $this->scope, 'purpose' => $this->purpose, 'claim' => $this->claim];
    }
}
