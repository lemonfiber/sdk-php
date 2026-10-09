<?php

declare(strict_types=1);

use Lemonfiber\Sdk\Client;
use Lemonfiber\Sdk\Contract\Api;
use Lemonfiber\Sdk\Exception\ConfigurationProblem;
use Lemonfiber\Sdk\Exception\Missing;
use Lemonfiber\Sdk\Exception\UnreadableResponse;
use Lemonfiber\Sdk\Generated\Kind;
use Lemonfiber\Sdk\Generated\RefusalCode;
use Lemonfiber\Sdk\Http\ReadRequest;
use Lemonfiber\Sdk\Picture;
use Saloon\Enums\Method;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

/**
 * The bytes a picture arrives as.
 */
const A_PICTURE = "\x89PNG\r\n\x1a\n";

/**
 * @param  array<string, string>  $headers
 * @return array{0: Client, 1: MockClient}
 */
function aClientShowing(string $bytes, array $headers, int $status = 200): array
{
    $mock = new MockClient([ReadRequest::class => MockResponse::make($bytes, $status, $headers)]);

    return [aClientOnPort(9000, 'a-run-token')->withMockClient($mock), $mock];
}

/**
 * The `error` envelope a picture nobody can show is refused with.
 */
function refusedAsAbsent(string $code): string
{
    return json_encode([
        'api_version' => Api::VERSION,
        'kind' => Kind::Error->value,
        'data' => [
            'code' => $code,
            'severity' => 'error',
            'state' => 'actionable',
            'summary' => 'There is nothing to show.',
            'meaning' => 'Nothing was read.',
            'remedies' => [['action' => 'Draw the title by its name']],
        ],
    ], JSON_THROW_ON_ERROR);
}

it('reads a poster as its bytes and the type it was labelled with', function (): void {
    [$client, $mock] = aClientShowing(A_PICTURE, ['Content-Type' => 'image/png']);

    $poster = $client->poster('4f2a9c', ['member' => 'ana']);
    $pending = $mock->getLastPendingRequest();

    expect($poster->bytes())->toBe(A_PICTURE)
        ->and($poster->mediaType())->toBe('image/png')
        ->and((string) $pending?->getUri())->toBe('http://127.0.0.1:9000/api/held/4f2a9c/poster?member=ana')
        ->and($pending?->getMethod())->toBe(Method::GET)
        ->and($pending?->headers()->get('Accept'))->toBe('image/jpeg, image/png, image/webp, image/gif, image/avif')
        ->and($pending?->headers()->get(Api::TOKEN_HEADER))->toBe('a-run-token');
});

it('reads a backdrop where a backdrop is', function (): void {
    [$client, $mock] = aClientShowing(A_PICTURE, ['Content-Type' => 'image/webp']);

    $backdrop = $client->backdrop('81', ['member' => 'ben']);

    expect($backdrop->mediaType())->toBe('image/webp')
        ->and((string) $mock->getLastPendingRequest()?->getUri())->toBe('http://127.0.0.1:9000/api/held/81/backdrop?member=ben');
});

it('reads a label in any case, and without its parameters', function (string $label, string $read): void {
    [$client] = aClientShowing(A_PICTURE, ['Content-Type' => $label]);

    expect($client->poster('81')->mediaType())->toBe($read);
})->with([
    'shouted' => ['IMAGE/JPEG', 'image/jpeg'],
    'with a parameter' => ['image/gif; q=1', 'image/gif'],
    'spaced' => [' image/avif ;x=y', 'image/avif'],
]);

it('refuses an answer that is not a raster picture', function (?string $label): void {
    [$client] = aClientShowing(A_PICTURE, $label === null ? [] : ['Content-Type' => $label]);

    expect(fn(): Picture => $client->poster('81'))->toThrow(UnreadableResponse::class);
})->with([
    'a drawing that runs script' => ['image/svg+xml'],
    'a page' => ['text/html'],
    'a type that starts like one' => ['image/pngx'],
    'no label at all' => [null],
]);

it('names the label it refused, and what a picture is', function (): void {
    [$client] = aClientShowing(A_PICTURE, ['Content-Type' => 'image/svg+xml']);

    expect(fn(): Picture => $client->poster('81'))->toThrow(
        UnreadableResponse::class,
        'The answer is not a picture this client shows: it was labelled "image/svg+xml", and a picture is one of image/jpeg, image/png, image/webp, image/gif, image/avif.',
    );
});

it('names an answer with no label as one', function (): void {
    [$client] = aClientShowing(A_PICTURE, []);

    expect(fn(): Picture => $client->poster('81'))->toThrow(
        UnreadableResponse::class,
        'The answer is not a picture this client shows: it was labelled with no type, and a picture is one of image/jpeg, image/png, image/webp, image/gif, image/avif.',
    );
});

it('keeps a picture of exactly the most a picture is', function (): void {
    $most = str_repeat('x', Picture::MOST_BYTES);
    [$client] = aClientShowing($most, ['Content-Type' => 'image/jpeg']);

    expect($client->poster('81')->bytes())->toBe($most);
});

it('refuses a picture one byte larger than the most a picture is', function (): void {
    [$client] = aClientShowing(str_repeat('x', Picture::MOST_BYTES + 1), ['Content-Type' => 'image/jpeg']);

    expect(fn(): Picture => $client->backdrop('81'))->toThrow(
        UnreadableResponse::class,
        'The answer is larger than the 2097152 bytes a picture is at most, so none of it was kept.',
    );
});

it('refuses a picture that states a length past the most a picture is, before reading any of it', function (string $length): void {
    [$client] = aClientShowing(A_PICTURE, ['Content-Type' => 'image/png', 'Content-Length' => $length]);

    expect(fn(): Picture => $client->poster('81'))->toThrow(
        UnreadableResponse::class,
        'The answer is larger than the 2097152 bytes a picture is at most, so none of it was kept.',
    );
})->with(['one byte past' => ['2097153'], 'far past' => ['99999999']]);

it('reads a picture whose stated length is not a whole number by what arrives', function (string $length): void {
    [$client] = aClientShowing(A_PICTURE, ['Content-Type' => 'image/png', 'Content-Length' => $length]);

    expect($client->poster('81')->bytes())->toBe(A_PICTURE);
})->with(['a word' => ['huge'], 'a negative' => ['-3000000'], 'exactly the most' => ['2097152']]);

it('raises a title nobody may see and a picture nobody has as missing, with the code saying which', function (string $code): void {
    [$client] = aClientShowing(refusedAsAbsent($code), ['Content-Type' => 'application/json'], 404);

    try {
        $client->poster('81', ['member' => 'ana']);
        $raised = null;
    } catch (Missing $missing) {
        $raised = $missing;
    }

    expect($raised?->code())->toBe(RefusalCode::from($code))
        ->and($raised?->refusal()?->code())->toBe($code)
        ->and($raised?->status())->toBe(404)
        ->and($raised?->endpoint())->toBe('/api/held/81/poster');
})->with(['outside the member\'s limits' => ['PLAY-2'], 'no picture of that kind' => ['PLAY-9']]);

it('sends nothing for an id that names no title', function (string $id): void {
    [$client, $mock] = aClientShowing(A_PICTURE, ['Content-Type' => 'image/png']);

    expect(fn(): Picture => $client->poster($id))->toThrow(
        ConfigurationProblem::class,
        sprintf('A title is named by the id its shelf lists it under. "%s" is not one: it names no path segment, so nothing was sent.', $id),
    )
        ->and(fn(): Picture => $client->backdrop($id))->toThrow(ConfigurationProblem::class)
        ->and($mock->getLastPendingRequest())->toBeNull();
})->with(['empty' => [''], 'this segment' => ['.'], 'the segment above' => ['..']]);
