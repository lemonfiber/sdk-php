<?php

declare(strict_types=1);

use Lemonfiber\Sdk\Contract\Api;
use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\Payload;
use Lemonfiber\Sdk\Exception\UnexpectedKind;
use Lemonfiber\Sdk\Generated\Contract;
use Lemonfiber\Sdk\Generated\Kind;
use Lemonfiber\Sdk\Generated\WordEnvelope;

it('hands over what an envelope of the named kind holds', function (): void {
    $envelope = new Envelope(Api::VERSION, 'word', 'pelican');

    expect(Payload::under(Kind::Word, $envelope))->toBe('pelican');
});

it('refuses an envelope carrying another kind, naming both', function (): void {
    $envelope = new Envelope(Api::VERSION, 'log', 'pelican');

    expect(fn(): mixed => Payload::under(Kind::Word, $envelope))
        ->toThrow(UnexpectedKind::class, 'carries the kind log and was read as word');
});

it('reaches a payload typed by its kind', function (): void {
    $term = [
        'word' => 'hardlink',
        'short' => 'Two names for one set of bytes.',
        'deep' => null,
        'also_called' => ['link'],
    ];
    $word = WordEnvelope::in(new Envelope(Api::VERSION, 'word', $term));

    // Only accepts the shape the contract gives `word`, so the call is the check:
    // widening the generated return type makes this line fail to analyse.
    $letters = static fn(string $text): int => strlen($text);

    expect($word->apiVersion)->toBe(Api::VERSION)
        ->and($word->kind)->toBe('word')
        ->and($letters($word->data['word']))->toBe(8)
        ->and($word->data['also_called'])->toBe(['link']);
});

it('refuses to read an envelope as a kind it does not carry', function (): void {
    $envelope = new Envelope(Api::VERSION, 'log', 'pelican');

    expect(fn(): Envelope => WordEnvelope::in($envelope))
        ->toThrow(UnexpectedKind::class, 'was read as word');
});

it('names every kind the contract describes, and one class each', function (): void {
    foreach (Kind::cases() as $kind) {
        expect(class_exists('Lemonfiber\\Sdk\\Generated\\' . $kind->name . 'Envelope'))->toBeTrue();
    }

    expect(Kind::cases())->not->toBeEmpty();
});

it('generates from the vendored artefact, and from the revision it came from', function (): void {
    $root = dirname(__DIR__, 2);
    $decoded = json_decode((string) file_get_contents($root . '/contract/web-api.contract.json'), true, 64, JSON_THROW_ON_ERROR);
    $artefact = is_array($decoded) ? $decoded : [];
    $kinds = $artefact['kinds'] ?? null;

    $vendored = is_array($kinds) ? array_keys($kinds) : [];
    $generated = array_map(static fn(Kind $kind): string => $kind->value, Kind::cases());
    sort($vendored);
    sort($generated);

    expect($artefact['api_version'] ?? null)->toBe(Contract::API_VERSION)
        ->and(Contract::SOURCE)->toBe(trim((string) file_get_contents($root . '/contract/VERSION')))
        ->and($vendored)->toBe($generated);
});
