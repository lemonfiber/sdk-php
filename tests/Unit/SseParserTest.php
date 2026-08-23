<?php

declare(strict_types=1);

use Lemonfiber\Sdk\Events\SseParser;

it('reads one event', function (): void {
    $events = new SseParser()->feed("event: status\ndata: {\"a\":1}\n\n");

    expect($events)->toHaveCount(1)
        ->and($events[0]->kind)->toBe('status')
        ->and($events[0]->data)->toBe('{"a":1}')
        ->and($events[0]->id)->toBeNull();
});

it('holds a partial line until the rest arrives', function (): void {
    $parser = new SseParser();

    expect($parser->feed("event: sta"))->toBe([])
        ->and($parser->feed("tus\ndata: one\n"))->toBe([]);

    $events = $parser->feed("\n");

    expect($events)->toHaveCount(1)
        ->and($events[0]->kind)->toBe('status')
        ->and($events[0]->data)->toBe('one');
});

it('joins several data lines with a line break', function (): void {
    $events = new SseParser()->feed("data: one\ndata: two\n\n");

    expect($events[0]->data)->toBe("one\ntwo");
});

it('calls an event without a name a message', function (): void {
    $events = new SseParser()->feed("data: one\n\n");

    expect($events[0]->kind)->toBe('message');
});

it('remembers the last id it was given', function (): void {
    $parser = new SseParser();
    $events = $parser->feed("id: 42\ndata: one\n\n");

    expect($events[0]->id)->toBe('42')
        ->and($parser->lastEventId())->toBe('42');
});

it('starts from the id it was handed', function (): void {
    $parser = new SseParser('7');

    expect($parser->lastEventId())->toBe('7')
        ->and($parser->feed("data: one\n\n")[0]->id)->toBe('7');
});

it('keeps the last id across an event that carries none', function (): void {
    $parser = new SseParser();
    $parser->feed("id: 42\ndata: one\n\n");

    expect($parser->feed("data: two\n\n")[0]->id)->toBe('42');
});

it('ignores an id holding a null', function (): void {
    $parser = new SseParser();
    $parser->feed("id: 42\ndata: one\n\n");
    $parser->feed("id: bad\0id\ndata: two\n\n");

    expect($parser->lastEventId())->toBe('42');
});

it('ignores a comment', function (): void {
    expect(new SseParser()->feed(": a sign of life\n\n"))->toBe([]);
});

it('ignores a field it does not know', function (): void {
    $events = new SseParser()->feed("retry: 3000\ndata: one\n\n");

    expect($events)->toHaveCount(1)
        ->and($events[0]->data)->toBe('one');
});

it('reads a field written with no value', function (): void {
    $events = new SseParser()->feed("data\ndata: one\n\n");

    expect($events[0]->data)->toBe("\none");
});

it('keeps a space that is not the one after the colon', function (): void {
    expect(new SseParser()->feed("data:  padded\n\n")[0]->data)->toBe(' padded');
});

it('reads a value written with no space after the colon', function (): void {
    expect(new SseParser()->feed("data:one\n\n")[0]->data)->toBe('one');
});

it('gives nothing for a blank line with no data behind it', function (): void {
    expect(new SseParser()->feed("event: status\n\n"))->toBe([]);
});

it('forgets a name that was never dispatched', function (): void {
    $parser = new SseParser();
    $parser->feed("event: status\n\n");

    expect($parser->feed("data: one\n\n")[0]->kind)->toBe('message');
});

it('reads lines however they end', function (string $chunk): void {
    $events = new SseParser()->feed($chunk);

    expect($events)->toHaveCount(1)
        ->and($events[0]->data)->toBe('one');
})->with([
    'line feed' => ["data: one\n\n"],
    'carriage return and line feed' => ["data: one\r\n\r\n"],
    'carriage return alone, with the stream continuing' => ["data: one\r\rdata: two\r"],
]);

it('treats a carriage return and line feed as one ending', function (): void {
    $events = new SseParser()->feed("data: one\r\ndata: two\r\n\r\n");

    expect($events)->toHaveCount(1)
        ->and($events[0]->data)->toBe("one\ntwo");
});

it('reads a field whose value is nothing at all', function (): void {
    $events = new SseParser()->feed("data:\n\n");

    expect($events)->toHaveCount(1)
        ->and($events[0]->data)->toBe('');
});

it('waits for the line feed that may follow a carriage return', function (): void {
    $parser = new SseParser();

    expect($parser->feed("data: one\r"))->toBe([]);

    $events = $parser->feed("\n\n");

    expect($events)->toHaveCount(1)
        ->and($events[0]->data)->toBe('one');
});

it('reads several events out of one arrival', function (): void {
    $events = new SseParser()->feed("data: one\n\ndata: two\n\n");

    expect($events)->toHaveCount(2)
        ->and($events[0]->data)->toBe('one')
        ->and($events[1]->data)->toBe('two');
});
