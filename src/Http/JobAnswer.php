<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Http;

use Lemonfiber\Sdk\Contract\Api;
use Lemonfiber\Sdk\Envelope\EnvelopeReader;
use Lemonfiber\Sdk\Exception\ApiVersionMismatch;
use Lemonfiber\Sdk\Exception\NoSuchJob;
use Lemonfiber\Sdk\Exception\UnreadableResponse;
use Lemonfiber\Sdk\JobStanding;
use Saloon\Http\Response;

use function str_contains;

/**
 * What asking after, or letting go of, the work one name stands for is answered with.
 *
 * The endpoint has two `404`s, and they are different answers. A name nobody
 * minted is said in prose, which is how lemonfiber labels what it says in its
 * own words, and is {@see NoSuchJob}. Work that stopped on a problem is the
 * `error` envelope at the status that problem warrants, `404` among them, and
 * is a refusal like any other.
 */
final readonly class JobAnswer
{
    /** What a name this run never handed out is answered with. */
    private const int NO_SUCH_NAME = 404;

    /** The status from which an answer is a refusal. */
    private const int REFUSED_FROM = 400;

    /**
     * Whether an answer is a refusal, which a name nobody minted is not.
     */
    public static function refused(Response $response): bool
    {
        return $response->status() >= self::REFUSED_FROM && ! self::noSuchName($response);
    }

    /**
     * Where the work stands, or that the name stands for nothing.
     *
     * @throws ApiVersionMismatch
     * @throws NoSuchJob
     * @throws UnreadableResponse
     */
    public static function standing(string $job, Response $response): JobStanding
    {
        if (self::noSuchName($response)) {
            throw NoSuchJob::inThisRun($job);
        }

        return JobStanding::of($job, $response->status(), new EnvelopeReader()->read($response->body()));
    }

    /**
     * Whether the answer is the surface saying in its own words that no work goes by the name.
     */
    private static function noSuchName(Response $response): bool
    {
        $type = Header::in($response, Header::CONTENT_TYPE);

        return $response->status() === self::NO_SUCH_NAME
            && ($type === null || ! str_contains($type, Api::JSON_MEDIA_TYPE));
    }
}
