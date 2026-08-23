<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Events;

use function implode;
use function str_contains;
use function str_starts_with;
use function strcspn;
use function strlen;
use function strpos;
use function substr;

/**
 * Turns arriving bytes into events, holding partial lines until the rest arrives.
 */
final class SseParser
{
    private const string DEFAULT_KIND = 'message';

    private const string LINE_TERMINATORS = "\r\n";

    private const string CARRIAGE_RETURN = "\r";

    private const string LINE_FEED = "\n";

    private string $buffer = '';

    private string $kind = '';

    /**
     * @var list<string>
     */
    private array $data = [];

    public function __construct(private ?string $lastEventId = null) {}

    /**
     * @return list<ServerEvent>
     */
    public function feed(string $chunk): array
    {
        $this->buffer .= $chunk;

        $events = [];

        while (($line = $this->takeLine()) !== null) {
            $event = $this->consume($line);

            if ($event instanceof ServerEvent) {
                $events[] = $event;
            }
        }

        return $events;
    }

    /**
     * The id of the last event that carried one.
     */
    public function lastEventId(): ?string
    {
        return $this->lastEventId;
    }

    private function takeLine(): ?string
    {
        $length = strcspn($this->buffer, self::LINE_TERMINATORS);

        if ($length === strlen($this->buffer)) {
            return null;
        }

        $skip = 1;

        if ($this->buffer[$length] === self::CARRIAGE_RETURN) {
            if ($length + 1 === strlen($this->buffer)) {
                return null;
            }

            if ($this->buffer[$length + 1] === self::LINE_FEED) {
                $skip = 2;
            }
        }

        $line = substr($this->buffer, 0, $length);
        $this->buffer = substr($this->buffer, $length + $skip);

        return $line;
    }

    private function consume(string $line): ?ServerEvent
    {
        if ($line === '') {
            return $this->dispatch();
        }

        $colon = strpos($line, ':');

        if ($colon === false) {
            $this->field($line, '');

            return null;
        }

        $value = substr($line, $colon + 1);

        $this->field(substr($line, 0, $colon), str_starts_with($value, ' ') ? substr($value, 1) : $value);

        return null;
    }

    private function field(string $name, string $value): void
    {
        if ($name === 'event') {
            $this->kind = $value;
        } elseif ($name === 'data') {
            $this->data[] = $value;
        } elseif ($name === 'id' && ! str_contains($value, "\0")) {
            $this->lastEventId = $value;
        }
    }

    private function dispatch(): ?ServerEvent
    {
        $data = $this->data;
        $kind = $this->kind;

        $this->data = [];
        $this->kind = '';

        if ($data === []) {
            return null;
        }

        return new ServerEvent(
            $kind === '' ? self::DEFAULT_KIND : $kind,
            implode(self::LINE_FEED, $data),
            $this->lastEventId,
        );
    }
}
