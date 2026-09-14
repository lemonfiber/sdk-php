<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk;

use Lemonfiber\Sdk\Contract\Api;
use Lemonfiber\Sdk\Exception\ConfigurationProblem;

use function trim;

/**
 * What a repairing run is being asked for: the offer, or the yes to one.
 *
 * The offer and the yes are one action, since they are one request read twice.
 * Unconfirmed it says what each repair would do and what else changes if it
 * does, and carries out none of it; confirmed it carries out what was agreed
 * to. So this is not a flag beside a call — it is the whole of what the call
 * means, and it is a type so that reading and acting cannot be told apart by
 * eye.
 *
 * **Three shapes, and lemonfiber refuses every other arrangement of the same
 * fields by name.** A yes that does not say which offer it answered cannot be
 * checked against the offer that stands, and an offer with none of it agreed
 * to is a yes that has lost its subject. Neither can be written here at all,
 * rather than being written and turned away.
 *
 * | built with | what travels | what happens |
 * |---|---|---|
 * | {@see self::offer()} | `confirm: false` | each repair is described; nothing is carried out |
 * | {@see self::agreedTo()} | `confirm: true`, `offer`, `agreed` | the named repairs out of that offer are carried out |
 * | {@see self::agreedInAdvance()} | `confirm: true` | every repair is carried out, with no offer read first |
 *
 * The offer names itself, and that name comes back as `agreement` on the
 * `repair` envelope. lemonfiber builds the name again from a fresh look before
 * it acts and refuses an agreement whose offer has moved on — so somebody who
 * agreed to something that has since changed is told, rather than having the
 * new thing carried out under the old yes.
 *
 * **{@see self::agreedInAdvance()} is the one that asks nobody.** It is what
 * the command line spells `--yes`: a decision taken before there was an offer
 * to read, rather than a way past being shown one. A run carried out under it
 * was agreed to by whoever wrote the call, not by whoever is at the screen.
 */
final readonly class Repair
{
    /**
     * The name lemonfiber offers this action under.
     */
    public const string ACTION = 'repair';

    /**
     * @param array<string, mixed> $arguments
     */
    private function __construct(private array $arguments) {}

    /**
     * Ask what could be put right, and put none of it right.
     */
    public static function offer(): self
    {
        return new self(['confirm' => false]);
    }

    /**
     * Agree to repairs out of the offer they were read in.
     *
     * At least one repair, said in the signature rather than checked for: an
     * agreement naming none is refused by lemonfiber, and a request that
     * cannot be written is one nobody has to be told about. Each is named as
     * the offer names it, which is the `check` on its entry of `offered`.
     *
     * The offer is the `agreement` that offer answered with, and it is
     * required: a yes carrying none of it is a yes to whatever stands at the
     * moment it lands, which is the one thing the two-step exists to prevent.
     *
     * @throws ConfigurationProblem
     */
    public static function agreedTo(string $offer, string $check, string ...$more): self
    {
        if (trim($offer) === '') {
            throw ConfigurationProblem::agreementNamesNoOffer();
        }

        return new self([
            'confirm' => true,
            'offer' => $offer,
            'agreed' => [$check, ...$more],
        ]);
    }

    /**
     * Agree to every repair, with no offer read first.
     */
    public static function agreedInAdvance(): self
    {
        return new self(['confirm' => true]);
    }

    /**
     * Where this is asked for.
     */
    public function endpoint(): string
    {
        return Api::action(self::ACTION);
    }

    /**
     * What this asks the action for.
     *
     * @return array<string, mixed>
     */
    public function arguments(): array
    {
        return $this->arguments;
    }
}
