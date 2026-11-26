<?php

declare(strict_types=1);

use Lemonfiber\Sdk\Contract\Api;
use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Generated\InvitationEnvelope;
use Lemonfiber\Sdk\Tests\Support\VendoredDefinition;

/**
 * A rehearsed invitation for one member, applying what it would, with its asking where one is given.
 *
 * @return array<string, mixed>
 */
function aRehearsedInvitation(?string $asking): array
{
    return [
        'address' => 'https://192.168.1.42/join',
        'hours' => 48,
        'linked' => 'not-tried',
        'name' => 'bo',
        'rehearsed' => true,
        'standing' => 'made',
        'suspended' => [],
        'withdrawn' => [],
        'applied' => [
            'filtering' => 'A limit hides what is rated above it; it is not a filter on what is unrated.',
            'libraries' => ['Films'],
            'limit' => 'PG',
            'requesting' => 'made',
            'unrated' => 'held-back',
            ...($asking === null ? [] : ['asking' => $asking]),
        ],
    ];
}

it('types what an invitation holds their asking to as a policy of the household, and leaves it optional', function (): void {
    expect(VendoredDefinition::property('invitation', 'InvitationApplied', 'asking'))->toHaveKey('anyOf')
        ->and(VendoredDefinition::requires('invitation', 'InvitationApplied', 'asking'))->toBeFalse();
});

it('reads the policy a rehearsal would hold their asking to, and its absence as no policy rather than one', function (?string $asking): void {
    $applied = InvitationEnvelope::in(new Envelope(Api::VERSION, 'invitation', aRehearsedInvitation($asking)))->data['applied'] ?? null;

    expect($applied['asking'] ?? null)->toBe($asking)
        ->and($applied['requesting'] ?? null)->toBe('made');
})->with([
    'held by the request service' => ['everything-waits'],
    'not held by it' => [null],
]);
