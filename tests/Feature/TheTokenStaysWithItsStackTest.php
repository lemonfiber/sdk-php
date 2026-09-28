<?php

declare(strict_types=1);

use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
use Lemonfiber\Sdk\Admission;
use Lemonfiber\Sdk\Client;
use Lemonfiber\Sdk\Contract\Api;
use Lemonfiber\Sdk\Exception\CertificateWasRefused;
use Lemonfiber\Sdk\Exception\ConfigurationProblem;
use Lemonfiber\Sdk\Exception\RequestFailed;
use Lemonfiber\Sdk\Exception\Unreachable;
use Lemonfiber\Sdk\Http\BaseUrl;
use Lemonfiber\Sdk\Http\CertificatePin;
use Lemonfiber\Sdk\Http\LemonfiberConnector;
use Lemonfiber\Sdk\Http\ReadRequest;
use Lemonfiber\Sdk\Http\RunToken;
use Lemonfiber\Sdk\Tests\Support\AnsweringListener;
use Psr\Http\Message\RequestInterface;
use Saloon\Contracts\Sender;
use Saloon\Enums\Method;
use Saloon\Http\Connector;
use Saloon\Http\PendingRequest;
use Saloon\Http\Request;
use Saloon\Http\Response;

const THE_TOKEN = 'the-stacks-own-token';

const THE_TOKEN_AS_SENT = 'X-Lemonfiber-Token: the-stacks-own-token';

const A_PIN_THE_PEER_DOES_NOT_PRESENT = 'bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb';

/**
 * Every option a request could ask the transport for that would turn the pin off,
 * hand the request to a handler that ignores it, or follow an answer elsewhere.
 *
 * @return array<string, mixed>
 */
function everyWayOfWeakeningIt(): array
{
    return [
        'handler' => HandlerStack::create(new CurlHandler()),
        'stream_context' => [],
        'verify' => false,
        'allow_redirects' => true,
    ];
}

/**
 * A request for an address a caller wrote out whole, on a connector told to allow one.
 */
function askingFor(string $address): Request
{
    return new class ($address) extends Request {
        public ?bool $allowBaseUrlOverride = true;

        protected Method $method = Method::GET;

        public function __construct(private readonly string $address) {}

        public function resolveEndpoint(): string
        {
            return $this->address;
        }
    };
}

/**
 * What one call raised, where it raised anything.
 *
 * @param Closure(): mixed $ask
 */
function whatAskingRaised(Closure $ask): ?Throwable
{
    try {
        $ask();
    } catch (Throwable $raised) {
        return $raised;
    }

    return null;
}

it('follows no answer that points away from the stack, and the token stays behind', function (Closure $build): void {
    /** @var array{0: AnsweringListener, 1: AnsweringListener, 2: Client} $built */
    $built = $build();
    [$stack, $elsewhere, $client] = $built;

    $raised = whatAskingRaised(static fn(): mixed => $client->read('/api/status'));

    expect($raised)->toBeInstanceOf(Unreachable::class)
        ->and($raised instanceof Unreachable ? $raised->reason() : '')->toBe('The answer was a redirect (302), and a redirect is not followed.')
        ->and($stack->heard())->toContain(THE_TOKEN_AS_SENT)
        ->and($elsewhere->heard())->toBe('');
})->with([
    'to another port' => [static function (): array {
        $elsewhere = AnsweringListener::plain();
        $stack = AnsweringListener::plain('127.0.0.1', 302, $elsewhere->address() . '/api/status');

        return [$stack, $elsewhere, Client::at($stack->address(), THE_TOKEN)];
    }],
    'to another host' => [static function (): array {
        $elsewhere = AnsweringListener::plain('[::1]');
        $stack = AnsweringListener::plain('127.0.0.1', 302, $elsewhere->address() . '/api/status');

        return [$stack, $elsewhere, Client::at($stack->address(), THE_TOKEN)];
    }],
    'to another scheme' => [static function (): array {
        $elsewhere = AnsweringListener::plain();
        $stack = AnsweringListener::encrypted(302, $elsewhere->address() . '/api/status');

        return [$stack, $elsewhere, Client::pinnedAt($stack->address(), THE_TOKEN, $stack->digest)];
    }],
]);

it('follows no answer that points away from a pinned stack, wherever it was asked', function (Closure $ask): void {
    $elsewhere = AnsweringListener::plain();
    $stack = AnsweringListener::encrypted(307, $elsewhere->address() . '/api/status');

    expect(whatAskingRaised(static fn(): mixed => $ask($stack)))->toBeInstanceOf(Unreachable::class)
        ->and($elsewhere->heard())->toBe('');
})->with([
    'live updates' => [static fn(AnsweringListener $stack): mixed => Client::pinnedAt($stack->address(), THE_TOKEN, $stack->digest)->eventSource()->open(null)],
    'the door' => [static fn(AnsweringListener $stack): mixed => Admission::at($stack->address(), $stack->digest)->open('a-password')],
]);

it('refuses a request addressed anywhere but the stack, and sends it nowhere', function (Closure $build): void {
    /** @var array{0: AnsweringListener, 1: AnsweringListener, 2: string, 3: string} $built */
    $built = $build();
    [$stack, $elsewhere, $address, $refused] = $built;
    $connector = new LemonfiberConnector(BaseUrl::fromString($stack->address()), RunToken::fromString(THE_TOKEN));
    $connector->allowBaseUrlOverride = true;

    $raised = whatAskingRaised(static fn(): mixed => $connector->send(askingFor($address)));

    expect($raised)->toBeInstanceOf(ConfigurationProblem::class)
        ->and($raised?->getMessage())->toBe(ConfigurationProblem::requestLeavesTheStack($refused)->getMessage())
        ->and($stack->heard())->toBe('')
        ->and($elsewhere->heard())->toBe('');
})->with([
    'another port' => [static function (): array {
        $stack = AnsweringListener::plain();
        $elsewhere = AnsweringListener::plain();

        return [$stack, $elsewhere, $elsewhere->address() . '/api/status', $elsewhere->address()];
    }],
    'another host' => [static function (): array {
        $stack = AnsweringListener::plain();
        $elsewhere = AnsweringListener::plain('[::1]');

        return [$stack, $elsewhere, $elsewhere->address() . '/api/status', $elsewhere->address()];
    }],
    'another scheme' => [static function (): array {
        $stack = AnsweringListener::plain();
        $elsewhere = AnsweringListener::plain();
        $encrypted = sprintf('https://127.0.0.1:%d', $stack->port);

        return [$stack, $elsewhere, $encrypted . '/api/status', $encrypted];
    }],
]);

it('refuses a request reshaped on its way out to go somewhere else, and names only where', function (): void {
    $stack = AnsweringListener::plain();
    $elsewhere = AnsweringListener::plain('[::1]');
    $connector = new LemonfiberConnector(BaseUrl::fromString($stack->address()), RunToken::fromString(THE_TOKEN));
    $reshaped = new class ($elsewhere->port) extends Request {
        protected Method $method = Method::GET;

        public function __construct(private readonly int $port) {}

        public function resolveEndpoint(): string
        {
            return '/api/status';
        }

        public function handlePsrRequest(RequestInterface $request, PendingRequest $pendingRequest): RequestInterface
        {
            return $request->withUri($request->getUri()
                ->withHost('[::1]')
                ->withPort($this->port)
                ->withUserInfo('someone', 'secret')
                ->withQuery('lines=5')
                ->withFragment('end'));
        }
    };

    $raised = whatAskingRaised(static fn(): mixed => $connector->send($reshaped));

    expect($raised?->getMessage())->toBe(ConfigurationProblem::requestLeavesTheStack($elsewhere->address())->getMessage())
        ->and($stack->heard())->toBe('')
        ->and($elsewhere->heard())->toBe('');
});

it('refuses a request addressed off the stack when it is sent without waiting too', function (): void {
    $stack = AnsweringListener::plain();
    $elsewhere = AnsweringListener::plain();
    $connector = new LemonfiberConnector(BaseUrl::fromString($stack->address()), RunToken::fromString(THE_TOKEN));
    $connector->allowBaseUrlOverride = true;

    $raised = whatAskingRaised(static fn(): mixed => $connector->sendAsync(askingFor($elsewhere->address() . '/api/status'))->wait());

    expect($raised)->toBeInstanceOf(ConfigurationProblem::class)
        ->and($elsewhere->heard())->toBe('');
});

it('sends a request that waits to the stack as it sends any other', function (): void {
    $stack = AnsweringListener::plain();
    $connector = new LemonfiberConnector(BaseUrl::fromString($stack->address()), RunToken::fromString(THE_TOKEN));

    $answer = $connector->sendAsync(new ReadRequest('/api/status'))->wait();

    expect($answer)->toBeInstanceOf(Response::class)
        ->and($stack->heard())->toContain(THE_TOKEN_AS_SENT);
});

it('holds a request to the pin whatever it, the connector or anything between asks of the transport', function (): void {
    $stack = AnsweringListener::encrypted();
    $connector = new LemonfiberConnector(
        BaseUrl::pinned($stack->address(), CertificatePin::fromSha256(A_PIN_THE_PEER_DOES_NOT_PRESENT)),
        RunToken::fromString(THE_TOKEN),
    );
    $connector->config()->merge(everyWayOfWeakeningIt());
    $connector->middleware()->onRequest(static function (PendingRequest $pending): void {
        $pending->config()->merge(everyWayOfWeakeningIt());
    });
    $request = new ReadRequest('/api/status');
    $request->config()->merge(everyWayOfWeakeningIt());

    expect(whatAskingRaised(static fn(): mixed => $connector->send($request)))->toBeInstanceOf(CertificateWasRefused::class)
        ->and($stack->heard())->toBe('');
});

it('writes nothing to a peer the pin does not name', function (): void {
    $stack = AnsweringListener::encrypted();

    expect(whatAskingRaised(static fn(): mixed => Client::pinnedAt($stack->address(), THE_TOKEN, A_PIN_THE_PEER_DOES_NOT_PRESENT)->read('/api/status')))
        ->toBeInstanceOf(CertificateWasRefused::class)
        ->and($stack->heard())->toBe('');
});

it('reads a peer presenting the pinned certificate, whatever the trust store would say of it', function (): void {
    $stack = AnsweringListener::encrypted();

    expect(Client::pinnedAt($stack->address(), THE_TOKEN, $stack->digest)->read('/api/status')->kind)->toBe('status')
        ->and($stack->heard())->toContain(THE_TOKEN_AS_SENT);
});

it('hands a refusal on as the stack answered it', function (): void {
    $stack = AnsweringListener::plain('127.0.0.1', 400, '-', 'That is not a question this stack answers.');

    $raised = whatAskingRaised(static fn(): mixed => Client::onPort($stack->port, THE_TOKEN)->read('/api/status'));

    expect($raised)->toBeInstanceOf(RequestFailed::class)
        ->and($raised instanceof RequestFailed ? $raised->status() : null)->toBe(400);
});

it('streams an answer only where the request asked for it to be streamed', function (bool $streamed): void {
    $stack = AnsweringListener::plain();
    $connector = new LemonfiberConnector(BaseUrl::onPort($stack->port), RunToken::fromString(THE_TOKEN));
    $request = new ReadRequest(Api::EVENTS_ENDPOINT);

    if ($streamed) {
        $request->config()->add('stream', true);
    }

    expect($connector->send($request)->getPsrResponse()->getBody()->isSeekable())->toBe(! $streamed);
})->with([
    'streamed' => [true],
    'read whole' => [false],
]);

it('hands the transport to no caller', function (ReflectionClass $class): void {
    $handedOut = [];

    foreach ($class->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
        $returned = $method->getReturnType();
        $named = $returned instanceof ReflectionNamedType ? $returned->getName() : '';

        if (is_a($named, Connector::class, true) || is_a($named, Sender::class, true)) {
            $handedOut[] = $method->getName();
        }
    }

    expect($handedOut)->toBe([]);
})->with([
    'the client' => [new ReflectionClass(Client::class)],
    'the door' => [new ReflectionClass(Admission::class)],
]);
