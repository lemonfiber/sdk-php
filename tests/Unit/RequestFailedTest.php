<?php

declare(strict_types=1);

use Lemonfiber\Sdk\Contract\Api;
use Lemonfiber\Sdk\Exception\RequestFailed;
use Lemonfiber\Sdk\Generated\Kind;

const NOTHING_WAS_TAKEN = 'lemonfiber turned down the request for /api/status and answered 500. Nothing was taken from that answer.';

/**
 * An envelope of a given kind and version, as it arrives on the wire.
 *
 * @param  array<string, mixed>|string  $data
 */
function answeredAs(string $kind, array|string $data, ?int $version = null): string
{
    return json_encode(
        ['api_version' => $version ?? Api::VERSION, 'kind' => $kind, 'data' => $data],
        JSON_THROW_ON_ERROR,
    );
}

/**
 * The `error` envelope lemonfiber answers a command that ran and failed with.
 *
 * @param  array<string, mixed>  $data
 */
function wentWrong(array $data): string
{
    return answeredAs(Kind::Error->value, $data);
}

it('hands on the sentence lemonfiber refused with', function (): void {
    $said = 'There is no action named `retry-imprt`. This surface offers what the command line offers, and nothing else.';

    $problem = RequestFailed::from('/api/actions/retry-imprt', 404, $said);

    expect($problem->said())->toBe($said)
        ->and($problem->getMessage())->toBe($said)
        ->and($problem->endpoint())->toBe('/api/actions/retry-imprt')
        ->and($problem->status())->toBe(404);
});

it('hands on the summary of a command that ran and failed', function (): void {
    $said = 'Sonarr would not accept the key this stack holds for it.';

    $problem = RequestFailed::from('/api/actions/check-everything', 500, wentWrong([
        'code' => 'service-refused-key',
        'summary' => $said,
        'meaning' => 'Sonarr is running but will not answer for this stack.',
        'remedies' => [],
        'severity' => 'error',
        'state' => 'actionable',
    ]));

    expect($problem->said())->toBe($said)
        ->and($problem->getMessage())->toBe($said);
});

it('takes a sentence without the space around it', function (string $body, string $said): void {
    expect(RequestFailed::from('/api/status', 400, $body)->said())->toBe($said);
})->with([
    'written as prose' => [
        "  The action `config-set` needs `key`, which was not given.\n",
        'The action `config-set` needs `key`, which was not given.',
    ],
    'carried by an envelope' => [
        wentWrong(['summary' => "\n  The `preset` given is not one this stack knows.  "]),
        'The `preset` given is not one this stack knows.',
    ],
]);

it('names the endpoint and the status where the answer carried no sentence', function (string $body): void {
    $problem = RequestFailed::from('/api/status', 500, $body);

    expect($problem->said())->toBeNull()
        ->and($problem->getMessage())->toBe(NOTHING_WAS_TAKEN);
})->with([
    'nothing at all' => [''],
    'only spaces' => ["  \n\t"],
    'a page from something standing in front of lemonfiber' => [
        '<html><head><title>502 Bad Gateway</title></head><body>nginx</body></html>',
    ],
    'an answer that breaks off' => ['{"api_version":1,"kind":'],
    'a list rather than an envelope' => ['[1,2]'],
    'an envelope of another kind' => [answeredAs(Kind::Status->value, ['summary' => 'Everything is healthy.'])],
    'an envelope this client cannot read' => [
        answeredAs(Kind::Error->value, ['summary' => 'Sonarr would not answer.'], Api::VERSION + 1),
    ],
    'an envelope whose payload is not a carrier' => [answeredAs(Kind::Error->value, 'gone wrong')],
    'an envelope carrying no summary' => [wentWrong([])],
    'an envelope whose summary is not a sentence' => [wentWrong(['summary' => 7])],
    'an envelope whose summary is only spaces' => [wentWrong(['summary' => '   '])],
]);
