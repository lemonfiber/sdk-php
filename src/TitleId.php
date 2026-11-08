<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk;

use function in_array;

use Lemonfiber\Sdk\Exception\ConfigurationProblem;

/**
 * A title's id, held to the one path segment a picture is read under.
 *
 * @internal
 */
final class TitleId
{
    /**
     * The ids that are not one path segment once a path is normalised.
     */
    private const array NO_SEGMENT = ['', '.', '..'];

    /**
     * The id, where it is one path segment. An empty id and the two dot segments are not:
     * a client or a proxy normalising the path would take each to a different read.
     *
     * @throws ConfigurationProblem
     */
    public static function segment(string $id): string
    {
        if (in_array($id, self::NO_SEGMENT, true)) {
            throw ConfigurationProblem::idNamesNoTitle($id);
        }

        return $id;
    }
}
