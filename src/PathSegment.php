<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk;

use function in_array;

use Lemonfiber\Sdk\Exception\ConfigurationProblem;

/**
 * A title's id or an ask's reference, held to the one path segment a picture is read under.
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
     * The id a title was listed under, where it is one path segment. An empty id and the two dot
     * segments are not: a client or a proxy normalising the path would take each to a different read.
     *
     * @throws ConfigurationProblem
     */
    public static function ofATitle(string $id): string
    {
        return self::isOne($id) ? $id : throw ConfigurationProblem::idNamesNoTitle($id);
    }

    /**
     * The reference a kept ask goes by, where it is one path segment, refused as an id is.
     *
     * @throws ConfigurationProblem
     */
    public static function ofAnAsk(string $reference): string
    {
        return self::isOne($reference) ? $reference : throw ConfigurationProblem::referenceNamesNoAsk($reference);
    }

    private static function isOne(string $named): bool
    {
        return ! in_array($named, self::NO_SEGMENT, true);
    }
}
