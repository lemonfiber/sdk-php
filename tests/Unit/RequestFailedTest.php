<?php

declare(strict_types=1);

use Lemonfiber\Sdk\Contract\Api;
use Lemonfiber\Sdk\Exception\AnswerUnusable;
use Lemonfiber\Sdk\Exception\ApiVersionMismatch;
use Lemonfiber\Sdk\Exception\Busy;
use Lemonfiber\Sdk\Exception\Declined;
use Lemonfiber\Sdk\Exception\Failed;
use Lemonfiber\Sdk\Exception\Misasked;
use Lemonfiber\Sdk\Exception\Missing;
use Lemonfiber\Sdk\Exception\NotAdmitted;
use Lemonfiber\Sdk\Exception\Problem;
use Lemonfiber\Sdk\Exception\RequestFailed;
use Lemonfiber\Sdk\Exception\TooManyAttempts;
use Lemonfiber\Sdk\Exception\UnexpectedKind;
use Lemonfiber\Sdk\Exception\UnreadableResponse;
use Lemonfiber\Sdk\Generated\Kind;
use Lemonfiber\Sdk\Generated\RefusalCode;
use Lemonfiber\Sdk\Tests\Support\GeneratedRefusals;

const A_CODE_THE_LIST_NAMES = 'FIXTURE-1';

GeneratedRefusals::loadWith(A_CODE_THE_LIST_NAMES, [
    'name' => 'LISTED_FOR_THESE_TESTS',
    'status' => 403,
    'description' => 'Raised by nothing; listed so these tests have a code the list names.',
]);

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

it('carries the whole problem document a command that failed was refused with', function (): void {
    $problem = RequestFailed::from('/api/jobs/k3n9v2xq', 500, wentWrong([
        'code' => 'bundle.leak',
        'severity' => 'critical',
        'state' => 'guided',
        'summary' => 'The bundle still held something that reads as a credential',
        'meaning' => 'Nothing has been written.',
        'remedies' => [['action' => 'Report which file this names']],
        'detail' => 'services.txt line 3 — nothing was written',
    ]));

    expect($problem->refusal()?->code())->toBe('bundle.leak')
        ->and($problem->refusal()?->detail())->toBe('services.txt line 3 — nothing was written')
        ->and($problem->said())->toBe('The bundle still held something that reads as a credential');
});

it('keeps the detail out of the message a logger takes by default', function (): void {
    $problem = RequestFailed::from('/api/jobs/k3n9v2xq', 500, wentWrong([
        'code' => 'service.said',
        'severity' => 'error',
        'state' => 'guided',
        'summary' => 'Sonarr refused the request.',
        'meaning' => 'Sonarr is running and said no.',
        'remedies' => [],
        'detail' => 'GET /api/v3/series?apikey=a-key-nobody-recognised failed',
    ]));

    expect($problem->getMessage())->toBe('Sonarr refused the request.')
        ->and((string) $problem)->not->toContain('a-key-nobody-recognised');
});

it('carries no problem document where the answer was not one', function (string $body): void {
    expect(RequestFailed::from('/api/status', 500, $body)->refusal())->toBeNull();
})->with([
    'nothing at all' => [''],
    'a sentence' => ['This needs the run token.'],
    'markup' => ['<html>bad gateway</html>'],
    'another kind' => [answeredAs('status', ['health' => 'healthy'])],
    'an envelope that cannot be read' => ['{"kind":'],
    'an error missing what the contract requires' => [wentWrong(['summary' => 'Something went wrong.'])],
]);

/**
 * An `error` envelope whose problem carries the given code.
 */
function refusedWith(string $code): string
{
    return wentWrong([
        'code' => $code,
        'severity' => 'error',
        'state' => 'actionable',
        'summary' => 'This run does not admit the session the request carried.',
        'meaning' => 'Nothing was read.',
        'remedies' => [['action' => 'Sign in again']],
    ]);
}

it('reads the code a refusal carries into the generated list', function (): void {
    $problem = RequestFailed::from('/api/status', 403, refusedWith(A_CODE_THE_LIST_NAMES));

    expect($problem->code())->not->toBeNull()
        ->and($problem->code())->toBe(RefusalCode::of(A_CODE_THE_LIST_NAMES))
        ->and($problem->code()?->status())->toBe(403);
});

it('reads a code the generated list does not name as no code, and keeps it on the problem', function (): void {
    $problem = RequestFailed::from('/api/status', 403, refusedWith('NOBODY-1'));

    expect($problem->code())->toBeNull()
        ->and($problem->refusal()?->code())->toBe('NOBODY-1')
        ->and($problem->status())->toBe(403);
});

it('carries no code where the answer carried no problem document', function (string $body): void {
    expect(RequestFailed::from('/api/status', 403, $body)->code())->toBeNull();
})->with([
    'nothing at all' => [''],
    'a sentence' => ['This needs the run token.'],
    'an error missing what the contract requires' => [wentWrong(['code' => A_CODE_THE_LIST_NAMES])],
]);

it('makes a refusal the family its status names', function (int $status, string $family): void {
    $refused = RequestFailed::from('/api/status', $status, 'Not like that.');

    expect($refused::class)->toBe($family)
        ->and($refused->status())->toBe($status)
        ->and($refused->getMessage())->toBe('Not like that.');
})->with([
    'asked wrongly' => [400, Misasked::class],
    'not there' => [404, Missing::class],
    'in the way of other work' => [409, Busy::class],
    'failed' => [500, Failed::class],
    'unavailable' => [503, Failed::class],
    'a status nothing else claims' => [418, Failed::class],
]);

it('reads a request turned away for its credential as not admitted', function (int $status, string $body): void {
    expect(RequestFailed::from('/api/status', $status, $body))->toBeInstanceOf(NotAdmitted::class);
})->with([
    'the credential\'s own code' => [403, refusedWith(RefusalCode::NotAdmitted->value)],
    'a code this client does not know' => [403, refusedWith('NOBODY-1')],
    'no document at all' => [401, ''],
    'a document with no sentence' => [403, wentWrong(['code' => A_CODE_THE_LIST_NAMES, 'summary' => '  '])],
]);

it('reads a request turned away with any other code as declined', function (int $status): void {
    $declined = RequestFailed::from('/api/actions/restart', $status, refusedWith(A_CODE_THE_LIST_NAMES));

    expect($declined)->toBeInstanceOf(Declined::class)
        ->and($declined->code())->toBe(RefusalCode::of(A_CODE_THE_LIST_NAMES));
})->with([401, 403]);

it('reads too many attempts with the wait the answer named', function (): void {
    $waiting = RequestFailed::from('/api/session', 429, refusedWith(RefusalCode::TooManyAttempts->value), 45);

    expect($waiting)->toBeInstanceOf(TooManyAttempts::class)
        ->and($waiting instanceof TooManyAttempts ? $waiting->seconds() : 0)->toBe(45)
        ->and($waiting->getMessage())->toContain('45 more seconds')
        ->and($waiting->code())->toBe(RefusalCode::TooManyAttempts)
        ->and($waiting->endpoint())->toBe('/api/session');
});

it('reads too many attempts with no wait as no wait, not as a guessed one', function (): void {
    $waiting = RequestFailed::from('/api/session', 429, '');

    expect($waiting)->toBeInstanceOf(TooManyAttempts::class)
        ->and($waiting instanceof TooManyAttempts ? $waiting->seconds() : 0)->toBeNull()
        ->and($waiting->getMessage())->toContain('did not say for how long');
});

it('counts every answer that cannot be used as one thing', function (Problem $problem): void {
    expect($problem)->toBeInstanceOf(AnswerUnusable::class);
})->with([
    'another version' => [ApiVersionMismatch::between(1, 2)],
    'not an envelope' => [UnreadableResponse::notAnEnvelope()],
    'another kind' => [UnexpectedKind::between('status', 'doctor')],
]);

it('counts no refusal as an answer that cannot be used', function (): void {
    expect(RequestFailed::from('/api/status', 500, 'Broke.'))->not->toBeInstanceOf(AnswerUnusable::class);
});
