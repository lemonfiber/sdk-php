<?php

declare(strict_types=1);

use Lemonfiber\Sdk\Contract\Api;
use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\JobStanding;

const A_NAME = 'k3n9v2xq';

/**
 * The answer work still going and work ended both arrive as.
 *
 * @return Envelope<mixed>
 */
function namedWork(): Envelope
{
    return new Envelope(Api::VERSION, 'job', ['job' => A_NAME, 'action' => 'repair']);
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
    // Positionally, which is all this repository's own analyser allows it:
    // `ergebnis.noNamedArgument` holds parameter names out of its own call
    // sites. A consumer is under no such rule and should name them, since the
    // two arms that take nothing are alike enough that position is a poor way
    // to tell them apart.
    return $standing->answering(
        static fn(): string => 'still going',
        static fn(Envelope $outcome): string => 'finished as ' . $outcome->kind,
        static fn(): string => 'ended',
    );
}

it('reads work still going from the status it is still going under', function (): void {
    expect(wordFor(JobStanding::of(A_NAME, 202, namedWork())))->toBe('still going');
});

it('reads work that ended apart from work still going, on the same envelope', function (): void {
    // The one to get right. Both answers are the `job` envelope and only the
    // status separates them, so a reading that took the kind for the answer
    // would poll a name nothing is doing until the stack is stopped.
    expect(wordFor(JobStanding::of(A_NAME, 200, namedWork())))->toBe('ended');
});

it('reads finished work apart from work that ended, on the same status', function (): void {
    // And both of these are 200, so a reading that took the status for the
    // answer would hand a caller an outcome that was never reached.
    expect(wordFor(JobStanding::of(A_NAME, 200, whatItCameTo())))->toBe('finished as repair');
});

it('hands the finished arm what the work came to', function (): void {
    $came = JobStanding::of(A_NAME, 200, whatItCameTo())->answering(
        static fn(): Envelope => whatItCameTo(),
        static fn(Envelope $outcome): Envelope => $outcome,
        static fn(): Envelope => namedWork(),
    );

    expect($came->kind)->toBe('repair')
        ->and($came->data)->toBe(['acted' => false, 'offered' => []]);
});

it('carries the name the work goes by, whichever standing it is in', function (): void {
    expect(JobStanding::of(A_NAME, 202, namedWork())->job)->toBe(A_NAME)
        ->and(JobStanding::of(A_NAME, 200, namedWork())->job)->toBe(A_NAME)
        ->and(JobStanding::of(A_NAME, 200, whatItCameTo())->job)->toBe(A_NAME);
});
