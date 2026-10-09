<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Http;

use Closure;

use function http_build_query;
use function implode;
use function in_array;
use function is_array;
use function is_bool;

use Iterator;
use Lemonfiber\Sdk\BundleFile;
use Lemonfiber\Sdk\Contract\Api;
use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\EnvelopeReader;
use Lemonfiber\Sdk\Exception\UnreadableResponse;
use Lemonfiber\Sdk\JobStanding;
use Lemonfiber\Sdk\Logs;
use Lemonfiber\Sdk\LogWindow;
use Lemonfiber\Sdk\Picture;
use Lemonfiber\Sdk\Time\Duration;
use Override;

use function parse_url;

use const PHP_URL_QUERY;

use Psr\Http\Message\RequestInterface;
use Saloon\Enums\Method;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Http\PendingRequest;
use Saloon\Http\Request;
use Saloon\Http\Response;

/**
 * A request that reads, mirroring a command's machine-readable output.
 *
 * A parameter given a list is the same key repeated once for each value, in
 * order (`form=a&form=b`), which is how a command's flag given more than once
 * is written as a read. An empty list sends nothing, as a null does. A
 * boolean is written `true` or `false`, the two words a stack reads as a choice.
 *
 * **A read is asked again before anything is reported**, twice, a little later
 * each time (the pause is the connector's), where nothing answered it or a gateway in front of the stack said
 * it could not reach it. Those are the failures a moment can clear: a phone
 * waking its radio, a proxy whose upstream is restarting. A read changes
 * nothing, so asking it again costs only the wait. Every other answer is the
 * stack's own and comes back on the first attempt: asking again would only
 * repeat it. An action is a different request: it is sent once (see
 * {@see ActRequest}).
 *
 * Each read is made by what its answer is: an envelope, the scrollback, a
 * file, the stream, or where a piece of work stands.
 *
 * @template-covariant T
 *
 * @implements ReadsItsAnswer<T>
 */
final class ReadRequest extends Request implements ReadsItsAnswer
{
    /** How many times a read is attempted in all: once, and twice more. */
    private const int ATTEMPTS = 3;

    /** What a gateway in front of the stack answers when it could not reach it, or is waiting for it. */
    private const array PASSED_ON = [502, 503, 504];

    #[Override]
    public ?int $tries = self::ATTEMPTS;

    #[Override]
    public ?bool $useExponentialBackoff = true;

    /**
     * The last answer is handed back rather than raised, so a stack's own refusal
     * reaches the caller the way it did before reads were retried.
     */
    #[Override]
    public ?bool $throwOnMaxTries = false;

    #[Override]
    protected Method $method = Method::GET;

    /**
     * @param array<string, scalar|list<scalar>|null> $parameters
     * @param Closure(Response): T $reading what the answer is read as
     * @param (Closure(Response): bool)|null $refusedWhen which answers are refusals, where that is not every status from 400
     */
    private function __construct(
        private readonly string $endpoint,
        private readonly array $parameters,
        private readonly Closure $reading,
        private readonly ?Closure $refusedWhen = null,
    ) {}

    /**
     * A read answered with one envelope.
     *
     * @param array<string, scalar|list<scalar>|null> $parameters
     *
     * @return self<Envelope<mixed>>
     */
    public static function envelope(string $endpoint, array $parameters = []): self
    {
        return new self($endpoint, $parameters, static fn(Response $answer): Envelope => new EnvelopeReader()->read($answer->body()));
    }

    /**
     * The scrollback, answered with an envelope per line, of the size asked for.
     *
     * @return self<LogWindow>
     */
    public static function logs(Logs $asked): self
    {
        return new self(
            Api::LOGS_ENDPOINT,
            $asked->parameters(),
            static fn(Response $answer): LogWindow => LogWindow::of($asked, new EnvelopeReader()->readEach($answer->body())),
        );
    }

    /**
     * One support bundle, handed over as its bytes beside what the transport said about them.
     *
     * @return self<BundleFile>
     */
    public static function bundle(string $name): self
    {
        return new self(Api::bundle($name), [], static fn(Response $answer): BundleFile => BundleFile::handedOver(
            $name,
            $answer->body(),
            Header::in($answer, Header::CONTENT_TYPE),
            Header::in($answer, Header::CONTENT_LENGTH),
        ));
    }

    /**
     * One of a title's pictures, asked for as one of the types a picture arrives as and read
     * no further than one byte past the most a picture is. An answer stating a longer length
     * is refused before any of it is read.
     *
     * @param array<string, scalar|list<scalar>|null> $parameters
     *
     * @return self<Picture>
     */
    public static function picture(string $endpoint, array $parameters = []): self
    {
        $request = new self($endpoint, $parameters, static function (Response $answer): Picture {
            if (Header::declaresMoreThan($answer, Picture::MOST_BYTES)) {
                throw UnreadableResponse::pictureTooLarge();
            }

            return Picture::handedOver(
                CappedReader::upTo($answer->stream(), Picture::MOST_BYTES),
                Header::in($answer, Header::CONTENT_TYPE),
            );
        });
        $request->headers()->add('Accept', implode(', ', Picture::MEDIA_TYPES));
        $request->config()->add('stream', true);

        return $request;
    }

    /**
     * Where the work one name stands for got to.
     *
     * @return self<JobStanding>
     */
    public static function job(string $job): self
    {
        return new self(
            Api::job($job),
            [],
            static fn(Response $answer): JobStanding => JobAnswer::standing($job, $answer),
            JobAnswer::refused(...),
        );
    }

    /**
     * The stream of live updates, read a chunk at a time and waiting at most `$wait` for each.
     *
     * @return self<Iterator<int, string>>
     */
    public static function events(?string $lastEventId, Duration $wait): self
    {
        $request = new self(
            Api::EVENTS_ENDPOINT,
            [],
            static fn(Response $answer): Iterator => ChunkedReader::from(StreamHandle::beneath($answer->stream()), $wait),
        );
        $request->headers()->add('Accept', Api::EVENT_STREAM_MEDIA_TYPE);
        $request->config()->add('stream', true);

        if ($lastEventId !== null) {
            $request->headers()->add(Api::RESUME_HEADER, $lastEventId);
        }

        return $request;
    }

    /**
     * @return T
     */
    #[Override]
    public function createDtoFromResponse(Response $response): mixed
    {
        return ($this->reading)($response);
    }

    #[Override]
    public function hasRequestFailed(Response $response): ?bool
    {
        return $this->refusedWhen instanceof Closure ? ($this->refusedWhen)($response) : null;
    }

    public function resolveEndpoint(): string
    {
        return $this->endpoint;
    }

    /**
     * The request with its query written as it is sent: the endpoint's own query
     * as written, then one pair for each value of each parameter.
     */
    #[Override]
    public function handlePsrRequest(RequestInterface $request, PendingRequest $pendingRequest): RequestInterface
    {
        $own = (string) parse_url($this->endpoint, PHP_URL_QUERY);
        $pairs = $own === '' ? [] : [$own];

        foreach ($this->parameters as $key => $value) {
            foreach (is_array($value) ? $value : [$value] as $one) {
                $pair = http_build_query([$key => $this->written($one)]);
                if ($pair !== '') {
                    $pairs[] = $pair;
                }
            }
        }

        return $request->withUri($request->getUri()->withQuery(implode('&', $pairs)));
    }

    /**
     * Whether this failure is one a moment can clear: nothing answered, or a
     * gateway could not reach the stack.
     */
    #[Override]
    public function handleRetry(FatalRequestException|RequestException $exception, Request $request): bool
    {
        return $exception instanceof FatalRequestException
            || in_array($exception->getResponse()->status(), self::PASSED_ON, true);
    }

    /**
     * @return array<string, scalar|list<scalar>|null>
     */
    protected function defaultQuery(): array
    {
        return $this->parameters;
    }

    /**
     * A value as a stack reads it, a boolean as its word rather than PHP's `1` or `0`.
     *
     * @param scalar|null $value
     */
    private function written(mixed $value): int|float|string|null
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        return $value;
    }
}
