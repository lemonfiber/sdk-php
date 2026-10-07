<?php

declare(strict_types=1);

use Lemonfiber\Sdk\Contract\Api;
use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\Payload;
use Lemonfiber\Sdk\Exception\UnexpectedKind;
use Lemonfiber\Sdk\Generated\Contract;
use Lemonfiber\Sdk\Generated\Kind;
use Lemonfiber\Sdk\Generated\NewsEnvelope;
use Lemonfiber\Sdk\Generated\NewsItemsEnvelope;
use Lemonfiber\Sdk\Generated\WordEnvelope;
use Lemonfiber\Sdk\Scripts\VendoredContract;

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
    $artefact = new VendoredContract($root)->artefact();
    $kinds = $artefact['kinds'] ?? null;

    $vendored = is_array($kinds) ? array_keys($kinds) : [];
    $generated = array_map(static fn(Kind $kind): string => $kind->value, Kind::cases());
    sort($vendored);
    sort($generated);

    expect($artefact['api_version'] ?? null)->toBe(Contract::API_VERSION)
        ->and(Contract::SOURCE)->toBe(trim((string) file_get_contents($root . '/contract/VERSION')))
        ->and($vendored)->toBe($generated);
});

it('reaches what is new as the read lists it, each kind by what names it', function (): void {
    $items = NewsItemsEnvelope::in(new Envelope(Api::VERSION, 'news-items', [
        'updates' => [['version' => '0.18.0', 'delivers' => 'Plugins, part three']],
        'requests' => [['number' => 12, 'title' => 'Dune', 'by' => 'Anna']],
        'problems' => [['check' => 'service.sonarr', 'onset' => '1759400000', 'summary' => 'Sonarr is stopped']],
        'unread' => [],
    ]));

    expect($items->kind)->toBe('news-items')
        ->and($items->data['updates'][0]['version'])->toBe('0.18.0')
        ->and($items->data['requests'][0]['number'])->toBe(12)
        ->and($items->data['problems'][0]['onset'])->toBe('1759400000');
});

it('reaches the newest of each kind as the stream says them, and the kinds it could not read', function (): void {
    $newest = NewsEnvelope::in(new Envelope(Api::VERSION, 'news', [
        'updates' => ['0.18.0', '0.17.2'],
        'requests' => [12, 7],
        'problems' => [['check' => 'service.sonarr', 'onset' => '1759400000']],
        'unread' => ['requests'],
    ]));

    expect($newest->kind)->toBe('news')
        ->and($newest->data['updates'])->toBe(['0.18.0', '0.17.2'])
        ->and($newest->data['requests'])->toBe([12, 7])
        ->and($newest->data['problems'][0]['check'])->toBe('service.sonarr')
        ->and($newest->data['unread'])->toBe(['requests']);
});
