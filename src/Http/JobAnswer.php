<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Http;

use function is_array;
use function json_decode;

use Lemonfiber\Sdk\Contract\Api;
use Lemonfiber\Sdk\Envelope\EnvelopeReader;
use Lemonfiber\Sdk\Exception\ApiVersionMismatch;
use Lemonfiber\Sdk\Exception\NoSuchJob;
use Lemonfiber\Sdk\Exception\UnreadableResponse;
use Lemonfiber\Sdk\Generated\RefusalCode;
use Lemonfiber\Sdk\JobStanding;
use Saloon\Http\Response;

use function str_contains;

/**
 * What asking after, or letting go of, the work one name stands for is answered with.
 *
 * The endpoint has two `404`s, and they are different answers. A name with no
 * work this caller may be told of behind it — one nobody minted, one forgotten,
 * one another credential started — is {@see NoSuchJob}: the `error` envelope
 * under {@see RefusalCode::NoSuchJob}, or the same said in prose. Work that
 * stopped on a problem is the `error` envelope under that problem's own code at
 * the status it warrants, `404` among them, and is a refusal like any other.
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
     * Whether the answer is the surface saying that no work goes by the name.
     */
    private static function noSuchName(Response $response): bool
    {
        if ($response->status() !== self::NO_SUCH_NAME) {
            return false;
        }

        $type = Header::in($response, Header::CONTENT_TYPE);
        if ($type === null || ! str_contains($type, Api::JSON_MEDIA_TYPE)) {
            return true;
        }

        $said = json_decode($response->body(), associative: true);
        $data = is_array($said) ? ($said['data'] ?? null) : null;

        return is_array($data) && ($data['code'] ?? null) === RefusalCode::NoSuchJob->value;
    }
}
