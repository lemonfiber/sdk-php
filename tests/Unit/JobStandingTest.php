<?php

declare(strict_types=1);

use Lemonfiber\Sdk\Contract\Api;
use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Exception\UnreadableResponse;
use Lemonfiber\Sdk\JobStanding;

const A_NAME = 'k3n9v2xq';

/**
 * The answer work still going arrives as.
 *
 * @return Envelope<mixed>
 */
function namedWork(): Envelope
{
    return new Envelope(Api::VERSION, 'job', ['job' => A_NAME, 'action' => 'repair']);
}

/**
 * The answer work that ended before it finished arrives as, for this reason.
 *
 * @return Envelope<mixed>
 */
function endedWork(string $why): Envelope
{
    return new Envelope(Api::VERSION, 'ended', ['job' => A_NAME, 'action' => 'repair', 'ended' => $why]);
}

/**
 * The answer finished work arrives as, which is the command's own envelope.
 *
 * @return Envelope<mixed>
 */
function whatItCameTo(): Envelope
{
    return new Envelope(Api::VERSION, 'repair', ['acted' => false, 'offered' => []]);
}

/**
 * Which of the three a standing is, as a word a test can compare.
 */
function wordFor(JobStanding $standing): string
{
    return $standing->answering(
        static fn(): string => 'still going',
        static fn(Envelope $outcome): string => 'finished as ' . $outcome->kind,
        static fn(string $why): string => 'ended: ' . $why,
    );
}

it('reads work still going from the status it is still going under', function (): void {
    expect(wordFor(JobStanding::of(A_NAME, 202, namedWork())))->toBe('still going');
});

it('reads work that ended apart from work still going, and says why it ended', function (string $why): void {
    expect(wordFor(JobStanding::of(A_NAME, 200, endedWork($why))))->toBe('ended: ' . $why);
})->with(['released', 'let-go', 'quiet']);

it('refuses a name answered at any status but the one work still going is answered at', function (int $status): void {
    expect(static fn(): JobStanding => JobStanding::of(A_NAME, $status, namedWork()))
        ->toThrow(UnreadableResponse::class, 'named the work at ' . $status);
})->with([200, 201, 203]);

it('reads finished work apart from work that ended, on the same status', function (): void {
    expect(wordFor(JobStanding::of(A_NAME, 200, whatItCameTo())))->toBe('finished as repair');
});

it('hands the finished arm what the work came to', function (): void {
    $came = JobStanding::of(A_NAME, 200, whatItCameTo())->answering(
        static fn(): Envelope => whatItCameTo(),
        static fn(Envelope $outcome): Envelope => $outcome,
        static fn(string $why): Envelope => endedWork($why),
    );

    expect($came->kind)->toBe('repair')
        ->and($came->data)->toBe(['acted' => false, 'offered' => []]);
});

it('carries the name the work goes by, whichever standing it is in', function (): void {
    expect(JobStanding::of(A_NAME, 202, namedWork())->job)->toBe(A_NAME)
        ->and(JobStanding::of(A_NAME, 200, endedWork('released'))->job)->toBe(A_NAME)
        ->and(JobStanding::of(A_NAME, 200, whatItCameTo())->job)->toBe(A_NAME);
});
