<?php

// Generated from the contract vendored under contract/. Do not edit.
// Contract::SOURCE names its revision and Contract::API_VERSION its wire version.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

/**
 * What a member's surface may not show, as the contract publishes it.
 */
final class HouseholdWords
{
    /**
     * Every service, engine and tunnel named, the bundle's parts with hyphens read as spaces.
     *
     * @var list<string>
     */
    public const array NAMES = [];

    /**
     * Every term for how the stack is built and run.
     *
     * @var list<string>
     */
    public const array TERMS = [];

    /**
     * Every unit a size or a rate is written in.
     *
     * @var list<string>
     */
    public const array SIZES = [];

    /**
     * Every mark that shows an address or a rate wherever it is written.
     *
     * @var list<string>
     */
    public const array MARKS = [];

    /**
     * Every name lemonfiber gives its own things, each with what it names, rendered in every locale as declared.
     *
     * @var array<string, string>
     */
    public const array DECLARED = [];

    /**
     * Texts a version, an address, a size or a rate makes unfit, each with what in it a member may not be shown.
     *
     * @var list<array{text: string, names: string}>
     */
    public const array EXAMPLES = [];
}
