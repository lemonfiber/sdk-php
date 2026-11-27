<?php

declare(strict_types=1);

use Lemonfiber\Sdk\Contract\Api;
use Lemonfiber\Sdk\DeviceAttestation;
use Lemonfiber\Sdk\Generated\RestartAction;
use Lemonfiber\Sdk\Http\ActRequest;
use Lemonfiber\Sdk\Http\AdmissionRequest;
use Lemonfiber\Sdk\Http\CodeAskRequest;
use Lemonfiber\Sdk\Http\CodeFinishRequest;
use Lemonfiber\Sdk\Http\DeviceKeyRequest;
use Lemonfiber\Sdk\Http\ForgetRequest;
use Lemonfiber\Sdk\Http\MintKeyRequest;
use Lemonfiber\Sdk\Http\PushEndpointRequest;
use Lemonfiber\Sdk\Http\ReadRequest;
use Lemonfiber\Sdk\Http\ReleaseRequest;
use Lemonfiber\Sdk\Http\RevokeKeyRequest;
use Lemonfiber\Sdk\KeyMint;
use Lemonfiber\Sdk\Logs;
use Lemonfiber\Sdk\MintProof;
use Lemonfiber\Sdk\PushEndpointRegistering;
use Lemonfiber\Sdk\WaitingCode;
use Saloon\Http\Request;

/**
 * Every route the vendored contract lists, as `METHOD path`.
 *
 * @return list<array{method: string, path: string}>
 */
function theRoutesListed(): array
{
    $listed = json_decode((string) file_get_contents(__DIR__ . '/../../contract/web-api/routes.json'), true, flags: JSON_THROW_ON_ERROR);
    $routes = [];
    foreach (is_array($listed) ? $listed : [] as $route) {
        if (is_array($route) && is_string($route['method'] ?? null) && is_string($route['path'] ?? null)) {
            $routes[] = ['method' => $route['method'], 'path' => $route['path']];
        }
    }

    return $routes;
}

/**
 * Whether the contract lists a route at `$method` whose path `$path` fills, one segment for each `{name}`.
 */
function isRouted(string $method, string $path): bool
{
    $asked = explode('/', $path);
    foreach (theRoutesListed() as $route) {
        $listed = explode('/', $route['path']);
        if ($route['method'] !== $method || count($listed) !== count($asked)) {
            continue;
        }
        $fills = array_map(static fn(string $part, string $given): bool => $part === $given || str_starts_with($part, '{'), $listed, $asked);
        if (! in_array(false, $fills, true)) {
            return true;
        }
    }

    return false;
}

/**
 * Each request class this client sends, with every request of it these tests build.
 *
 * @return array<class-string<Request>, list<Request>>
 */
function everyRequestBuilt(): array
{
    return [
        ActRequest::class => [new ActRequest(new RestartAction())],
        AdmissionRequest::class => [new AdmissionRequest('the password')],
        CodeAskRequest::class => [new CodeAskRequest()],
        CodeFinishRequest::class => [new CodeFinishRequest(WaitingCode::of('a-handle'))],
        DeviceKeyRequest::class => [
            DeviceKeyRequest::registering('pixel', 'key', DeviceAttestation::ios("\xa3"), 'the password'),
            DeviceKeyRequest::challenging('pixel'),
            DeviceKeyRequest::revoking('pixel'),
        ],
        ForgetRequest::class => [new ForgetRequest('a1b2')],
        MintKeyRequest::class => [new MintKeyRequest(KeyMint::of('den', 'read', 'other'), MintProof::password('the password'))],
        PushEndpointRequest::class => [
            PushEndpointRequest::registering(PushEndpointRegistering::of('pixel', 'https://push.example/a', "\x04", "\x01")),
            PushEndpointRequest::removing('pixel'),
        ],
        ReadRequest::class => [
            ReadRequest::logs(Logs::ofService('sonarr', 10)),
            ReadRequest::bundle('lemonfiber-bundle.tar.gz'),
            ReadRequest::picture(Api::poster('a1')),
            ReadRequest::picture(Api::backdrop('a1')),
            ReadRequest::picture(Api::askablePoster('a1')),
            ReadRequest::picture(Api::requestPoster('a1')),
        ],
        ReleaseRequest::class => [new ReleaseRequest('a1b2')],
        RevokeKeyRequest::class => [new RevokeKeyRequest('ha')],
    ];
}

it('builds a request of every request class this client sends', function (): void {
    $classes = [];
    $files = glob(__DIR__ . '/../../src/Http/*Request.php');
    foreach (is_array($files) ? $files : [] as $file) {
        $classes[] = 'Lemonfiber\\Sdk\\Http\\' . basename($file, '.php');
    }
    $sent = array_values(array_filter($classes, static fn(string $class): bool => is_subclass_of($class, Request::class)));
    sort($sent);
    $built = array_keys(everyRequestBuilt());
    sort($built);

    expect($built)->toBe($sent);
});

it('sends every request at a route the contract lists, at the method it lists it at', function (): void {
    $unlisted = [];
    foreach (everyRequestBuilt() as $requests) {
        foreach ($requests as $request) {
            $asked = $request->getMethod()->value . ' ' . $request->resolveEndpoint();
            if (! isRouted($request->getMethod()->value, $request->resolveEndpoint())) {
                $unlisted[] = $asked;
            }
        }
    }

    expect($unlisted)->toBe([]);
});

it('holds every path this client names to a read the contract lists or the base a name is composed onto', function (): void {
    $named = array_filter(new ReflectionClass(Api::class)->getConstants(), static fn(mixed $value, string $name): bool => str_ends_with($name, '_ENDPOINT') && is_string($value), ARRAY_FILTER_USE_BOTH);
    $paths = array_map(static fn(array $route): string => $route['path'], theRoutesListed());
    $unlisted = array_values(array_filter($named, static fn(string $path): bool => ! isRouted('GET', $path)
        && array_filter($paths, static fn(string $listed): bool => str_starts_with($listed, $path . '/{')) === []));

    expect(count($named))->toBeGreaterThan(25)
        ->and($unlisted)->toBe([]);
});

it('tells a route the contract does not list apart', function (): void {
    expect(isRouted('DELETE', '/api/keys/ha'))->toBeTrue()
        ->and(isRouted('POST', '/api/keys/ha'))->toBeFalse()
        ->and(isRouted('DELETE', '/api/keys'))->toBeFalse()
        ->and(isRouted('DELETE', '/api/keys/ha/more'))->toBeFalse();
});
