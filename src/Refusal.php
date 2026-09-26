<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk;

use function array_is_list;
use function is_array;
use function is_string;

use UnexpectedValueException;

/**
 * The problem document lemonfiber refused a request with, whole.
 *
 * Every field the `error` envelope carries is here, as it arrived: the code,
 * how much it matters, where it stands, the sentence, what it means, what to
 * do, the technical detail and the problem beneath it. A field lemonfiber left
 * out is absent here too, and nothing is filled in for it.
 *
 * A document missing a field the contract requires, or carrying one in a
 * shape the contract does not describe, is not read at all: there is no
 * refusal rather than part of one.
 */
final readonly class Refusal
{
    /**
     * @param list<Remedy> $remedies
     */
    private function __construct(
        private string $code,
        private string $severity,
        private string $state,
        private string $summary,
        private string $meaning,
        private array $remedies,
        private ?string $detail,
        private ?self $cause,
    ) {}

    /**
     * The problem an `error` envelope's payload describes, or none where it
     * does not describe one this client can read.
     */
    public static function from(mixed $data): ?self
    {
        try {
            return self::read($data);
        } catch (UnexpectedValueException) {
            return null;
        }
    }

    /**
     * The stable identifier for this kind of problem.
     */
    public function code(): string
    {
        return $this->code;
    }

    /**
     * How much it matters, in lemonfiber's word for it.
     */
    public function severity(): string
    {
        return $this->severity;
    }

    /**
     * Where it stands with respect to being fixed, in lemonfiber's word for it.
     */
    public function state(): string
    {
        return $this->state;
    }

    /**
     * What happened, in one plain sentence.
     */
    public function summary(): string
    {
        return $this->summary;
    }

    /**
     * What it means for the operator.
     */
    public function meaning(): string
    {
        return $this->meaning;
    }

    /**
     * What to do, most likely first.
     *
     * @return list<Remedy>
     */
    public function remedies(): array
    {
        return $this->remedies;
    }

    /**
     * The underlying technical detail, or none where lemonfiber gave none.
     *
     * Possibly sensitive. It quotes what a service itself said, with the
     * secrets lemonfiber recognises withheld, and recognising them is best
     * effort: a secret in a shape it does not know can still be here. It is fit
     * to show to the person who asked, and not to log, report or forward.
     */
    public function detail(): ?string
    {
        return $this->detail;
    }

    /**
     * The problem beneath this one, or none where lemonfiber named none.
     */
    public function cause(): ?self
    {
        return $this->cause;
    }

    /**
     * @throws UnexpectedValueException
     */
    private static function read(mixed $data): self
    {
        if (! is_array($data)) {
            throw new UnexpectedValueException('A problem is a table.');
        }

        $cause = self::optional($data, 'cause');

        return new self(
            self::text($data, 'code'),
            self::text($data, 'severity'),
            self::text($data, 'state'),
            self::text($data, 'summary'),
            self::text($data, 'meaning'),
            self::remediesIn($data),
            self::optionalText($data, 'detail'),
            $cause === null ? null : self::read($cause),
        );
    }

    /**
     * @param  array<mixed> $data
     * @return list<Remedy>
     *
     * @throws UnexpectedValueException
     */
    private static function remediesIn(array $data): array
    {
        $listed = $data['remedies'] ?? null;

        if (! is_array($listed) || ! array_is_list($listed)) {
            throw new UnexpectedValueException('A problem lists its remedies.');
        }

        $remedies = [];

        foreach ($listed as $remedy) {
            if (! is_array($remedy)) {
                throw new UnexpectedValueException('A remedy is a table.');
            }

            $remedies[] = new Remedy(self::text($remedy, 'action'), self::optionalText($remedy, 'detail'));
        }

        return $remedies;
    }

    /**
     * A field that must be text.
     *
     * @param array<mixed> $data
     *
     * @throws UnexpectedValueException
     */
    private static function text(array $data, string $field): string
    {
        $value = $data[$field] ?? null;

        if (! is_string($value)) {
            throw new UnexpectedValueException('A required field is not text.');
        }

        return $value;
    }

    /**
     * A field that is text where it is given, and absent where it is not.
     *
     * @param array<mixed> $data
     *
     * @throws UnexpectedValueException
     */
    private static function optionalText(array $data, string $field): ?string
    {
        $value = self::optional($data, $field);

        if ($value !== null && ! is_string($value)) {
            throw new UnexpectedValueException('An optional field is not text.');
        }

        return $value;
    }

    /**
     * A field as it arrived, where it arrived, and nothing where it did not.
     *
     * Given as `null` and left out are one answer, which is the contract's.
     *
     * @param array<mixed> $data
     */
    private static function optional(array $data, string $field): mixed
    {
        return $data[$field] ?? null;
    }
}
