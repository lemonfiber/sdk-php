<?php

declare(strict_types=1);

use Lemonfiber\Sdk\Exception\ConfigurationProblem;
use Lemonfiber\Sdk\Http\BaseUrl;
use Lemonfiber\Sdk\Http\HostResolver;

it('builds an address from a port on this machine', function (): void {
    expect(BaseUrl::onPort(7777)->toString())->toBe('http://127.0.0.1:7777');
});

it('accepts the lowest and the highest port', function (): void {
    expect(BaseUrl::onPort(1)->toString())->toBe('http://127.0.0.1:1')
        ->and(BaseUrl::onPort(65535)->toString())->toBe('http://127.0.0.1:65535');
});

it('refuses a port below the lowest', function (): void {
    expect(fn(): BaseUrl => BaseUrl::onPort(0))
        ->toThrow(ConfigurationProblem::class, '0 falls outside that range');
});

it('refuses a port above the highest', function (): void {
    expect(fn(): BaseUrl => BaseUrl::onPort(65536))
        ->toThrow(ConfigurationProblem::class, '65536 falls outside that range');
});

it('accepts a written out loopback address', function (string $given, string $expected): void {
    expect(BaseUrl::fromString($given)->toString())->toBe($expected);
})->with([
    'plain' => ['http://127.0.0.1:9000', 'http://127.0.0.1:9000'],
    'no port' => ['http://127.0.0.1', 'http://127.0.0.1'],
    'anywhere in the loopback range' => ['http://127.4.5.6:80', 'http://127.4.5.6:80'],
    'over tls' => ['https://127.0.0.1:9000', 'https://127.0.0.1:9000'],
    'with a path' => ['http://127.0.0.1:9000/lemonfiber', 'http://127.0.0.1:9000/lemonfiber'],
    'trailing slash trimmed' => ['http://127.0.0.1:9000/', 'http://127.0.0.1:9000'],
    'the sixth version of loopback' => ['http://[::1]:9000', 'http://[::1]:9000'],
]);

it('refuses an address on another machine', function (string $given, string $host): void {
    expect(fn(): BaseUrl => BaseUrl::fromString($given, resolvingTo(['93.184.216.34'])))
        ->toThrow(ConfigurationProblem::class, 'The address "' . $host . '" points somewhere else');
})->with([
    'a public name' => ['http://example.com:9000', 'example.com'],
    'a private address' => ['http://192.168.1.10:9000', '192.168.1.10'],
    'every interface' => ['http://0.0.0.0:9000', '0.0.0.0'],
    'a near miss' => ['http://127.example.com:9000', '127.example.com'],
    'the sixth version of every interface' => ['http://[::]:9000', '[::]'],
    'loopback mapped into the sixth version' => ['http://[::ffff:127.0.0.1]:9000', '[::ffff:127.0.0.1]'],
]);

it('refuses a scheme it does not speak', function (): void {
    expect(fn(): BaseUrl => BaseUrl::fromString('ftp://127.0.0.1:9000'))
        ->toThrow(ConfigurationProblem::class, 'This one starts with "ftp"');
});

it('refuses an address with no scheme at all', function (): void {
    expect(fn(): BaseUrl => BaseUrl::fromString('127.0.0.1:9000'))
        ->toThrow(ConfigurationProblem::class, 'has to start with http or https');
});

it('names the missing scheme as nothing at all', function (): void {
    expect(fn(): BaseUrl => BaseUrl::fromString('//127.0.0.1:9000'))
        ->toThrow(ConfigurationProblem::class, 'This one starts with ""');
});

it('names the missing host as nothing at all', function (): void {
    expect(fn(): BaseUrl => BaseUrl::fromString('http:'))
        ->toThrow(ConfigurationProblem::class, 'The address "" points somewhere else');
});

it('refuses an address it cannot read', function (): void {
    expect(fn(): BaseUrl => BaseUrl::fromString('http://:9000'))
        ->toThrow(ConfigurationProblem::class, 'could not be read');
});

it('refuses an address carrying anything beyond a host, a port and a path', function (string $given): void {
    expect(fn(): BaseUrl => BaseUrl::fromString($given))
        ->toThrow(ConfigurationProblem::class, 'may carry a host, a port and a path, and nothing else');
})->with([
    'sign-in details' => ['http://operator:secret@127.0.0.1:9000'],
    'a user only' => ['http://operator@127.0.0.1:9000'],
    'a query' => ['http://127.0.0.1:9000?token=secret'],
    'a fragment' => ['http://127.0.0.1:9000#secret'],
]);

// ARCH-R60: a name that resolves to loopback is a loopback address. Refusing the
// word rejects what an operator types; resolving before connecting is the guard.

/**
 * @param list<string> $addresses
 */
function resolvingTo(array $addresses): HostResolver
{
    return new readonly class ($addresses) implements HostResolver {
        /**
         * @param list<string> $addresses
         */
        public function __construct(private array $addresses) {}

        /**
         * @return list<string>
         */
        public function addressesFor(string $host): array
        {
            return $this->addresses;
        }
    };
}

it('accepts a name that resolves to loopback', function (string $host, array $to): void {
    /** @var list<string> $to */
    expect(BaseUrl::fromString('http://' . $host . ':7777', resolvingTo($to))->toString())
        ->toBe('http://' . $host . ':7777');
})->with([
    'localhost on IPv4' => ['localhost', ['127.0.0.1']],
    'a name on the alternate loopback' => ['lemonfiber.local', ['127.0.0.53']],
    'several, all loopback' => ['localhost', ['127.0.0.1', '127.0.0.2']],
]);

it('refuses a name that resolves anywhere but loopback', function (array $to): void {
    /** @var list<string> $to */
    expect(fn(): BaseUrl => BaseUrl::fromString('http://elsewhere.test:7777', resolvingTo($to)))
        ->toThrow(ConfigurationProblem::class);
})->with([
    'off the machine' => [['93.184.216.34']],
    'one of several is off the machine' => [['127.0.0.1', '93.184.216.34']],
]);

it('refuses a name that resolves to nothing', function (): void {
    expect(fn(): BaseUrl => BaseUrl::fromString('http://nowhere.test:7777', resolvingTo([])))
        ->toThrow(ConfigurationProblem::class);
});

it('refuses a literal address that is not loopback without resolving it', function (): void {
    expect(fn(): BaseUrl => BaseUrl::fromString('http://93.184.216.34:7777', resolvingTo(['127.0.0.1'])))
        ->toThrow(ConfigurationProblem::class);
});


it('accepts localhost through the machine\'s own resolver', function (): void {
    expect(BaseUrl::fromString('http://localhost:7777')->toString())
        ->toBe('http://localhost:7777');
});
