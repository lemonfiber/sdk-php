<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Mutation;

use function arsort;
use function file_get_contents;
use function implode;
use function ksort;
use function max;
use function sort;
use function substr_count;

/**
 * The mutation shards packed into as few runners as hold them.
 *
 * Each runner starts PHP, installs the dependencies and runs the suite under
 * coverage before it makes a mutant, and for the smaller shards that start-up
 * is most of the job, on runners the whole organisation shares. Packed, the
 * small shards share one start-up and the slowest runner is no slower.
 *
 * First fit over shards taken in falling order of weight: each goes to the
 * first runner it fits in, or starts one. No runner holds more than the largest
 * shard does alone, so the runner that shard sits in is the slowest there would
 * have been anyway. Ties are broken by name, so a commit always lists the same
 * runners in the same order.
 */
final class Runners
{
    /**
     * Each runner, named by its shards in name order joined by `$together`.
     *
     * @param array<string, int> $weights the source each shard holds, by shard
     *
     * @return list<string>
     */
    public static function pack(array $weights, string $together): array
    {
        ksort($weights);
        arsort($weights);

        $size = $weights === [] ? 0 : max($weights);
        $runners = [];
        $held = [];

        foreach ($weights as $name => $weight) {
            $at = self::firstThatHolds($held, $weight, $size);

            if ($at === null) {
                $runners[] = [$name];
                $held[] = $weight;

                continue;
            }

            $runners[$at][] = $name;
            $held[$at] += $weight;
        }

        $names = [];

        foreach ($runners as $members) {
            sort($members);
            $names[] = implode($together, $members);
        }

        return $names;
    }

    /**
     * How much source each shard holds, in lines, and every file that could not
     * be read to say.
     *
     * @param array<string, list<string>> $shards
     *
     * @return array{array<string, int>, list<string>}
     */
    public static function weigh(array $shards): array
    {
        $weights = [];
        $unreadable = [];

        foreach ($shards as $name => $files) {
            $weights[$name] = 0;

            foreach ($files as $file) {
                $text = file_get_contents($file);

                if ($text === false) {
                    $unreadable[] = $file;

                    continue;
                }

                $weights[$name] += substr_count($text, "\n");
            }
        }

        return [$weights, $unreadable];
    }

    /**
     * The first runner with room for this much more, or null where none has.
     *
     * @param array<int, int> $held what each runner already holds
     */
    private static function firstThatHolds(array $held, int $weight, int $size): ?int
    {
        foreach ($held as $at => $already) {
            if ($already + $weight <= $size) {
                return $at;
            }
        }

        return null;
    }
}
