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
use function is_string;
use function sprintf;
use function str_starts_with;
use function strlen;
use function strval;
use function substr;

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
     * Every reference pointing at a definition the kind carrying it does not hold.
     *
     * An unresolvable reference is not an error anywhere below this. `SchemaTypes`
     * answers one with `mixed`, and `union()` collapses any union holding a `mixed`
     * to `mixed` entire — so a contract that moved its definitions somewhere this
     * does not look would generate a whole surface of `mixed`, exit nought, and be
     * committed by the bump that fetched it. Nothing else would notice: `src/Generated`
     * is excluded from PHPStan, and the suite checks kind names rather than shapes.
     *
     * Hoisting `$defs` to the document root is exactly that change, and is a thing
     * somebody may reasonably try. This is what makes it fail loudly and say where.
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

            foreach ($this->referenced($schema) as $name) {
                if (! array_key_exists($name, $carried)) {
                    $dangling[] = sprintf('%s -> %s', strval($kind), $name);
                }
            }
        }

        return $dangling;
    }

    /**
     * The name of every definition a schema points at, however deeply, in order.
     *
     * @param  array<mixed, mixed>  $node
     * @return list<string>
     */
    private function referenced(array $node): array
    {
        $prefix = '#/$defs/';
        $found = [];

        foreach ($node as $key => $value) {
            if ($key === '$ref' && is_string($value) && str_starts_with($value, $prefix)) {
                $found[] = substr($value, strlen($prefix));
            } elseif (is_array($value)) {
                $found = [...$found, ...$this->referenced($value)];
            }
        }

        return $found;
    }
}
