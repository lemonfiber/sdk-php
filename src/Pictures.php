<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk;

use Lemonfiber\Sdk\Contract\Api;
use Lemonfiber\Sdk\Exception\ConfigurationProblem;
use Lemonfiber\Sdk\Exception\RequestFailed;
use Lemonfiber\Sdk\Exception\Unreachable;
use Lemonfiber\Sdk\Exception\UnreadableResponse;
use Lemonfiber\Sdk\Http\LemonfiberConnector;
use Lemonfiber\Sdk\Http\ReadRequest;

/**
 * The pictures one running lemonfiber hands over: a title's poster and backdrop, a found title's
 * poster and a kept ask's cover, each one of {@see Picture::MEDIA_TYPES} of at most
 * {@see Picture::MOST_BYTES}.
 */
final readonly class Pictures
{
    public function __construct(private LemonfiberConnector $connector) {}

    /**
     * A title's poster.
     *
     * The id is the one the shelf lists the title under, and the query takes `member` and
     * `defaults` as the title read does. A title outside the member's limits is {@see Exception\Missing}
     * with `PLAY-2`, and a title with no poster is {@see Exception\Missing} with `PLAY-9`.
     *
     * @param  array<string, scalar|list<scalar>|null>  $query
     *
     * @throws ConfigurationProblem
     * @throws RequestFailed
     * @throws Unreachable
     * @throws UnreadableResponse
     */
    public function poster(string $id, array $query = []): Picture
    {
        return $this->read(Api::poster(PathSegment::ofATitle($id)), $query);
    }

    /**
     * A title's backdrop, read and refused as {@see self::poster()} is.
     *
     * @param  array<string, scalar|list<scalar>|null>  $query
     *
     * @throws ConfigurationProblem
     * @throws RequestFailed
     * @throws Unreachable
     * @throws UnreadableResponse
     */
    public function backdrop(string $id, array $query = []): Picture
    {
        return $this->read(Api::backdrop(PathSegment::ofATitle($id)), $query);
    }

    /**
     * The poster of a title a member's search offered.
     *
     * The id is the one the search page listed the title under, and the query takes `kind` as the
     * page named it, `unit` beside it for music and books, and `member` as the search does. A title
     * outside the member's limits is {@see Exception\Missing} with `WISH-3`, and a title with no
     * poster is {@see Exception\Missing} with `WISH-13`.
     *
     * @param  array<string, scalar|list<scalar>|null>  $query
     *
     * @throws ConfigurationProblem
     * @throws RequestFailed
     * @throws Unreachable
     * @throws UnreadableResponse
     */
    public function askablePoster(string $id, array $query = []): Picture
    {
        return $this->read(Api::askablePoster(PathSegment::ofATitle($id)), $query);
    }

    /**
     * The cover of an ask for music or books the core keeps, after the search that found it is gone.
     *
     * The reference is the one the household list names the ask with, and the query takes
     * `member`, which a member's own session replaces with themselves. A reference no kept ask goes
     * by, or one another member made, is {@see Exception\Missing} with `WISH-3`.
     *
     * @param  array<string, scalar|list<scalar>|null>  $query
     *
     * @throws ConfigurationProblem
     * @throws RequestFailed
     * @throws Unreachable
     * @throws UnreadableResponse
     */
    public function requestPoster(string $reference, array $query = []): Picture
    {
        return $this->read(Api::requestPoster(PathSegment::ofAnAsk($reference)), $query);
    }

    /**
     * @param  array<string, scalar|list<scalar>|null>  $query
     *
     * @throws RequestFailed
     * @throws Unreachable
     * @throws UnreadableResponse
     */
    private function read(string $endpoint, array $query): Picture
    {
        return $this->connector->answer(ReadRequest::picture($endpoint, $query));
    }
}
