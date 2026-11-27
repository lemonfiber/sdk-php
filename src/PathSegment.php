<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk;

use function in_array;

use Lemonfiber\Sdk\Exception\ConfigurationProblem;

/**
 * A name a caller hands over, held to the one path segment a route names it by.
 *
 * Refused where it is empty or a dot segment, which a client or a proxy normalising
 * the path would take to a different route. {@see Contract\Api}
 * encodes what is held, so whatever else it holds, a `/`, a `?`, a `#` or a `%`
 * among it, names one segment and never another route.
 *
 * @internal
 */
final class PathSegment
{
    /**
     * The names that are not one path segment once a path is normalised.
     */
    private const array NO_SEGMENT = ['', '.', '..'];

    /**
     * The id a title was listed under, where it is one path segment.
     *
     * @throws ConfigurationProblem
     */
    public static function ofATitle(string $id): string
    {
        return self::held($id, 'A title is named by the id it was listed under.');
    }

    /**
     * The reference a kept ask goes by, where it is one path segment.
     *
     * @throws ConfigurationProblem
     */
    public static function ofAnAsk(string $reference): string
    {
        return self::held($reference, 'A kept ask is named by the reference the household list gave it.');
    }

    /**
     * The name an accepting reply gave work, where it is one path segment.
     *
     * @throws ConfigurationProblem
     */
    public static function ofAJob(string $job): string
    {
        return self::held($job, 'Work is named by the name its accepting reply gave it.');
    }

    /**
     * The name a listing gave an integration key, a device key or a push endpoint, or a support
     * bundle's, where it is one path segment.
     *
     * @throws ConfigurationProblem
     */
    public static function ofAName(string $name): string
    {
        return self::held($name, 'What a listing names is named by the name it gave.');
    }

    /**
     * @throws ConfigurationProblem
     */
    private static function held(string $named, string $namedBy): string
    {
        return in_array($named, self::NO_SEGMENT, true)
            ? throw ConfigurationProblem::namesNoPathSegment($namedBy, $named)
            : $named;
    }
}
