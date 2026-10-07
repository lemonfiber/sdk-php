<?php

declare(strict_types=1);

use Lemonfiber\Sdk\Http\BaseUrl;
use Lemonfiber\Sdk\Http\Call;
use Lemonfiber\Sdk\Http\LemonfiberConnector;
use Lemonfiber\Sdk\Http\ReadRequest;
use Lemonfiber\Sdk\Tests\Support\FakeClock;
use Lemonfiber\Sdk\Time\Duration;
use Saloon\Http\PendingRequest;

it('carries a call on the request it is a call to, and no other', function (): void {
    $connector = new LemonfiberConnector(BaseUrl::onPort(9000), aWait());
    $asked = ReadRequest::envelope('/api/status');
    $other = ReadRequest::envelope('/api/status');

    $call = Call::on($asked, Duration::ofSeconds(1), new FakeClock([0.0]));

    expect(Call::of(new PendingRequest($connector, $asked)))->toBe($call)
        ->and(Call::of(new PendingRequest($connector, $other)))->toBeNull();
});

it('gives what is left of its wait, and nothing once it is over', function (): void {
    $call = Call::on(ReadRequest::envelope('/api/status'), Duration::ofSeconds(2), new FakeClock([10.0, 10.5, 13.0]));

    expect($call->secondsLeft())->toBe(1.5)
        ->and($call->secondsLeft())->toBe(0.0);
});

it('asks again only while the doubling pause still leaves room', function (): void {
    // A wait of one second, and a first pause of 300 ms: 0.3 and 0.6 fit, 1.2 does not.
    $call = Call::on(ReadRequest::envelope('/api/status'), Duration::ofSeconds(1), new FakeClock([0.0]));

    expect($call->hasRoomToAskAgain(300))->toBeTrue()
        ->and($call->hasRoomToAskAgain(300))->toBeTrue()
        ->and($call->hasRoomToAskAgain(300))->toBeFalse();
});

it('starts every call with a whole wait of its own', function (): void {
    $clock = new FakeClock([0.0, 0.9, 0.9, 0.9]);
    $first = Call::on(ReadRequest::envelope('/api/status'), Duration::ofSeconds(1), $clock);

    $second = Call::on(ReadRequest::envelope('/api/status'), Duration::ofSeconds(1), $clock);

    expect(round($first->secondsLeft(), 6))->toBe(0.1)
        ->and(round($second->secondsLeft(), 6))->toBe(1.0);
});
