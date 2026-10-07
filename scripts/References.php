<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Scripts;

use function array_filter;
use function array_key_exists;
use function array_keys;
use function array_map;
use function array_values;
use function implode;
use function in_array;
use function is_array;
use function json_encode;
use function sprintf;
use function strval;

/**
 * What the references in a contract's kinds are checked for before anything is
 * generated from them.
 */
final readonly class References
{
    /**
     * Keywords that describe a schema without constraining what it matches.
     *
     * @var list<string>
     */
    private const array ANNOTATIONS = ['description', 'title', 'default', 'examples'];

    /**
     * Every reference in a schema with a constraint sitting beside it.
     *
     * Draft-07 readers discard whatever accompanies a `$ref` and 2020-12 readers
     * apply both, so the shape means two different things to two readers. This
     * generator is one of them: it takes the reference and drops the constraint,
     * which loses the tag telling two variants of one payload apart.
     *
     * @param  array<mixed, mixed>  $node
     * @return list<string>
     */
    public function besideAReference(array $node, string $path): array
    {
        $constraints = array_values(array_filter(
            array_map(strval(...), array_keys($node)),
            static fn(string $key): bool => $key !== '$ref' && ! in_array($key, self::ANNOTATIONS, true),
        ));

        $found = array_key_exists('$ref', $node) && $constraints !== []
            ? [sprintf('%s (%s)', $path, implode(', ', $constraints))]
            : [];

        foreach ($node as $key => $value) {
            if (is_array($value)) {
                $found = [...$found, ...$this->besideAReference($value, $path . '/' . strval($key))];
            }
        }

        return $found;
    }

    /**
     * A reference as the contract writes it, for a refusal to name.
     */
    public static function written(mixed $reference): string
    {
        return json_encode($reference, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }

    /**
     * Every reference, in whatever form, that resolves to no definition the kind carrying it holds.
     *
     * `SchemaTypes` refuses such a reference too, but only one it reaches from a
     * payload; this reads every reference a kind holds, reached or not, and names
     * them all at once. An unresolvable reference is refused rather than typed
     * `mixed` because `union()` collapses any union holding a `mixed` to `mixed`
     * entire, so one would turn a whole surface to `mixed` with nothing else to
     * notice: `src/Generated` is excluded from PHPStan, and the suite checks kind
     * names rather than shapes.
     *
     * A cycle needs no exception: a definition pointing back at one it is reached
     * from still points at a definition this holds, and `ShapePlan` leaves the
     * reference closing the cycle `mixed`.
     *
     * @param  array<mixed, mixed>  $kinds
     * @return list<string>
     */
    public function pointingAtNothing(array $kinds): array
    {
        $dangling = [];

        foreach ($kinds as $kind => $schema) {
            if (! is_array($schema)) {
                continue;
            }

            $defs = $schema['$defs'] ?? null;
            $carried = is_array($defs) ? $defs : [];

            foreach ($this->referenced($schema) as $reference) {
                $name = ShapePlan::definitionIn($reference);

                if ($name === null || ! is_array($carried[$name] ?? null)) {
                    $dangling[] = sprintf('kind `%s` -> %s', strval($kind), self::written($reference));
                }
            }
        }

        return $dangling;
    }

    /**
     * Every reference a schema holds, however deeply, in order.
     *
     * @param  array<mixed, mixed>  $node
     * @return list<mixed>
     */
    private function referenced(array $node): array
    {
        $found = [];

        foreach ($node as $key => $value) {
            if ($key === '$ref') {
                $found[] = $value;
            } elseif (is_array($value)) {
                $found = [...$found, ...$this->referenced($value)];
            }
        }

        return $found;
    }
}
