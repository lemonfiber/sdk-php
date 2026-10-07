<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Scripts;

use function is_array;
use function sprintf;

use UnexpectedValueException;

/**
 * The PHP source a contract's kinds are written as.
 *
 * Every file opens with the same header naming where it came from, so a reader
 * who finds one of these knows the artefact and the revision behind it.
 */
final readonly class GeneratedSource
{
    private const string NAMESPACE = 'Lemonfiber\\Sdk\\Generated';

    public function __construct(
        private string $artefact,
        private string $stamp,
        private int $version,
        private SchemaTypes $types = new SchemaTypes(),
    ) {}

    /**
     * @param  array<mixed, mixed>  $schema
     *
     * @throws UnexpectedValueException naming the reference, where the payload holds one that resolves to no definition the kind carries
     */
    public function envelopeClass(string $kind, string $name, array $schema): string
    {
        $properties = $schema['properties'] ?? null;
        $payload = is_array($properties) ? $properties['data'] ?? null : null;
        $defs = $schema['$defs'] ?? null;
        $named = is_array($defs) ? $defs : [];

        $type = is_array($payload)
            ? $this->types->typeOf($payload, $named)
            : SchemaTypes::UNKNOWN;

        return $this->header() . sprintf(
            <<<'PHP'

                use Lemonfiber\Sdk\Envelope\Envelope;
                use Lemonfiber\Sdk\Envelope\Payload;
                use Lemonfiber\Sdk\Exception\UnexpectedKind;

                /**
                 * The `%s` envelope, shaped as the contract describes it.
                 *
                 * @phpstan-type Data %s
                 */
                final class %sEnvelope
                {
                    /**
                     * The kind an envelope must carry to be read as this one.
                     */
                    public const Kind KIND = Kind::%s;

                    /**
                     * The same envelope with its payload typed by its kind.
                     *
                     * @param  Envelope<mixed>  $envelope
                     * @return Envelope<Data>
                     *
                     * @throws UnexpectedKind
                     */
                    public static function in(Envelope $envelope): Envelope
                    {
                        /** @var Data $data */
                        $data = Payload::under(self::KIND, $envelope);

                        return new Envelope($envelope->apiVersion, $envelope->kind, $data);
                    }
                }

                PHP,
            $kind,
            $type,
            $name,
            $name,
        );
    }

    /**
     * @param  array<string, string>  $named  class name to the kind it came from
     */
    public function kindEnum(array $named): string
    {
        $cases = '';

        foreach ($named as $name => $kind) {
            $cases .= sprintf("    case %s = %s;\n", $name, $this->types->quoted($kind));
        }

        return $this->header() . sprintf(
            <<<'PHP'

                /**
                 * Every kind the contract describes, and the only ones this package reads.
                 */
                enum Kind: string
                {
                %s}

                PHP,
            $cases,
        );
    }

    /**
     * @param  array<string, array{code: string, status: int, description: string}>  $refusals  case name to the refusal it came from
     */
    public function refusalEnum(array $refusals): string
    {
        $cases = '';

        foreach ($refusals as $name => $refusal) {
            $cases .= sprintf("    case %s = %s;\n", $name, $this->types->quoted($refusal['code']));
        }

        return $this->header() . sprintf(
            <<<'PHP'

                /**
                 * Every code the contract lists a refusal as carrying.
                 *
                 * A code this list does not name is read by its status alone. What the
                 * contract says of each code is in {@see RefusalStatus} and
                 * {@see RefusalDescription}.
                 */
                enum RefusalCode: string
                {
                %s
                    /**
                     * The case a code names, or none where there is no code or one this list does not name.
                     */
                    public static function of(?string $code): ?self
                    {
                        return $code === null ? null : self::tryFrom($code);
                    }

                    /**
                     * The status a refusal carrying this code is answered with.
                     */
                    public function status(): int
                    {
                        return RefusalStatus::of($this);
                    }

                    /**
                     * The registry's own line about this code.
                     */
                    public function description(): string
                    {
                        return RefusalDescription::of($this);
                    }
                }

                PHP,
            $cases,
        );
    }

    /**
     * @param  array<string, array{code: string, status: int, description: string}>  $refusals  case name to the refusal it came from
     */
    public function refusalStatusClass(array $refusals): string
    {
        $arms = '';

        foreach ($refusals as $name => $refusal) {
            $arms .= sprintf("            RefusalCode::%s => %d,\n", $name, $refusal['status']);
        }

        return $this->refusalTable('RefusalStatus', 'The status each refusal code is answered with.', 'int', $arms);
    }

    /**
     * @param  array<string, array{code: string, status: int, description: string}>  $refusals  case name to the refusal it came from
     */
    public function refusalDescriptionClass(array $refusals): string
    {
        $arms = '';

        foreach ($refusals as $name => $refusal) {
            $arms .= sprintf("            RefusalCode::%s => %s,\n", $name, $this->types->quoted($refusal['description']));
        }

        return $this->refusalTable('RefusalDescription', 'The registry\'s own line about each refusal code.', 'string', $arms);
    }

    public function contractClass(): string
    {
        return $this->header() . sprintf(
            <<<'PHP'

                /**
                 * What the vendored contract artefact states about itself.
                 */
                final class Contract
                {
                    /**
                     * The wire version these types were generated from.
                     */
                    public const int API_VERSION = %d;

                    /**
                     * The lemonfiber revision the artefact was vendored from.
                     */
                    public const string SOURCE = %s;
                }

                PHP,
            $this->version,
            $this->types->quoted($this->stamp),
        );
    }

    /**
     * One thing the contract says of every refusal code, as a class answering it for a code.
     */
    private function refusalTable(string $class, string $summary, string $type, string $arms): string
    {
        return $this->header() . sprintf(
            <<<'PHP'

                /**
                 * %s
                 */
                final class %s
                {
                    public static function of(RefusalCode $code): %s
                    {
                        return match ($code) {
                %s        };
                    }
                }

                PHP,
            $summary,
            $class,
            $type,
            $arms,
        );
    }

    private function header(): string
    {
        return sprintf(
            <<<'PHP'
                <?php

                // Generated from %s. Do not edit.
                // Source: %s, api_version %d.
                // Regenerate with `composer contract:generate`.

                declare(strict_types=1);

                namespace %s;

                PHP,
            $this->artefact,
            $this->stamp,
            $this->version,
            self::NAMESPACE,
        );
    }
}
