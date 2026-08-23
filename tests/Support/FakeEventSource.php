<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Tests\Support;

use function array_shift;

use ArrayIterator;
use Iterator;
use Lemonfiber\Sdk\Events\EventSource;

/**
 * A stream handing out chunks a test decided in advance, one connection at a time.
 */
final class FakeEventSource implements EventSource
{
    /**
     * @var list<string|null>
     */
    private array $resumedFrom = [];

    /**
     * @param  list<list<string>>  $connections
     */
    public function __construct(private array $connections) {}

    public function open(?string $lastEventId): Iterator
    {
        $this->resumedFrom[] = $lastEventId;

        return new ArrayIterator(array_shift($this->connections) ?? []);
    }

    /**
     * The id each connection was asked to resume from.
     *
     * @return list<string|null>
     */
    public function resumedFrom(): array
    {
        return $this->resumedFrom;
    }
}
