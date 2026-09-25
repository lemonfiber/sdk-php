<?php

declare(strict_types=1);

use Lemonfiber\Sdk\Admission;
use Lemonfiber\Sdk\Client;
use Lemonfiber\Sdk\Contract\Api;
use Lemonfiber\Sdk\Exception\Unreachable;
use Lemonfiber\Sdk\Logs;
use Lemonfiber\Sdk\Repair;

/**
 * A port on this machine that nothing is listening on.
 *
 * Taken from the system and let go of again, so the connection is refused at
 * once rather than waited on.
 */
function aPortNothingListensOn(): int
{
    $server = stream_socket_server('tcp://127.0.0.1:0');

    if ($server === false) {
        throw new RuntimeException('no port could be taken to let go of');
    }

    $name = (string) stream_socket_get_name($server, false);
    fclose($server);

    return (int) substr($name, (int) strrpos($name, ':') + 1);
}

/**
 * What one call raised, where nothing answered it.
 *
 * @param Closure(): mixed $ask
 */
function whatNothingAnsweringRaises(Closure $ask): Throwable
{
    try {
        $ask();
    } catch (Throwable $raised) {
        return $raised;
    }

    throw new RuntimeException('the call came back as though something had answered it');
}

it('raises one of its own problems wherever nothing answers', function (string $endpoint, Closure $ask): void {
    $raised = whatNothingAnsweringRaises(static fn(): mixed => $ask(aPortNothingListensOn()));

    expect($raised)->toBeInstanceOf(Unreachable::class)
        ->and($raised->getPrevious())->toBeNull();

    if ($raised instanceof Unreachable) {
        expect($raised->endpoint())->toBe($endpoint)
            ->and($raised->reason())->not->toBe('');
    }
})->with([
    'a read' => ['/api/clients', static fn(int $port): mixed => Client::onPort($port, 'a-run-token')->read('/api/clients')],
    'an action' => ['/api/actions/restart', static fn(int $port): mixed => Client::onPort($port, 'a-run-token')->act('/api/actions/restart')],
    'a repair' => [Repair::offer()->endpoint(), static fn(int $port): mixed => Client::onPort($port, 'a-run-token')->repair(Repair::offer())],
    'asking after work' => [Api::job('a-job'), static fn(int $port): mixed => Client::onPort($port, 'a-run-token')->whatBecameOf('a-job')],
    'letting work go' => [Api::job('a-job'), static fn(int $port): mixed => Client::onPort($port, 'a-run-token')->letGoOf('a-job')],
    'the logs' => [Api::LOGS_ENDPOINT, static fn(int $port): mixed => Client::onPort($port, 'a-run-token')->logs(Logs::ofService('sonarr', 5))],
    'live updates' => [Api::EVENTS_ENDPOINT, static fn(int $port): mixed => Client::onPort($port, 'a-run-token')->eventSource()->open(null)],
    'the door' => [Admission::ENDPOINT, static fn(int $port): mixed => Admission::onPort($port)->open('a-password')],
]);

it('keeps a pinned address one of its own problems where nothing answers', function (): void {
    $raised = whatNothingAnsweringRaises(static fn(): mixed => Client::pinnedAt(
        sprintf('https://127.0.0.1:%d', aPortNothingListensOn()),
        'a-run-token',
        str_repeat('a', 64),
    )->read('/api/status'));

    expect($raised)->toBeInstanceOf(Unreachable::class);
});

it('says nothing of the token or the password in what the connection reported', function (): void {
    $port = aPortNothingListensOn();
    $read = whatNothingAnsweringRaises(static fn(): mixed => Client::onPort($port, 'a-run-token')->read('/api/status'));
    $door = whatNothingAnsweringRaises(static fn(): mixed => Admission::onPort($port)->open('a-password'));

    expect($read->getMessage() . ($read instanceof Unreachable ? $read->reason() : ''))->not->toContain('a-run-token')
        ->and($door->getMessage() . ($door instanceof Unreachable ? $door->reason() : ''))->not->toContain('a-password');
});
