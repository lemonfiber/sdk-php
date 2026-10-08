<?php

declare(strict_types=1);

use Lemonfiber\Sdk\ActionRequest;
use Lemonfiber\Sdk\Admission;
use Lemonfiber\Sdk\Client;
use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Events\HeldValue;
use Lemonfiber\Sdk\Events\ServerEvent;
use Lemonfiber\Sdk\Exception\AnswerUnusable;
use Lemonfiber\Sdk\Exception\Problem;
use Lemonfiber\Sdk\Exception\RequestFailed;
use Lemonfiber\Sdk\Http\BaseUrl;
use Lemonfiber\Sdk\Http\CertificatePin;
use Lemonfiber\Sdk\Http\IdempotencyKey;
use Lemonfiber\Sdk\Http\RunToken;
use Lemonfiber\Sdk\Time\Duration;

arch('every file declares strict types')
    ->expect('Lemonfiber\Sdk')
    ->toUseStrictTypes();

arch('every class is final')
    ->expect('Lemonfiber\Sdk')
    ->classes()
    ->toBeFinal()
    ->ignoring(['Lemonfiber\Sdk\Tests', RequestFailed::class, ActionRequest::class]);

arch('a refusal is one of its families')
    ->expect(RequestFailed::class)
    ->toBeAbstract();

arch('an action is one the contract lists')
    ->expect(ActionRequest::class)
    ->toBeAbstract();

arch('nothing prints or halts')
    ->expect(['dd', 'dump', 'var_dump', 'die', 'exit', 'print_r', 'var_export', 'sleep', 'usleep'])
    ->not->toBeUsed();

arch('values are readonly')
    ->expect([
        Envelope::class,
        HeldValue::class,
        ServerEvent::class,
        BaseUrl::class,
        CertificatePin::class,
        IdempotencyKey::class,
        RunToken::class,
        Duration::class,
    ])
    ->toBeReadonly();

arch('the contract holds no behaviour')
    ->expect('Lemonfiber\Sdk\Contract')
    ->toOnlyUse(['Lemonfiber\Sdk\Generated']);

arch('generated types know nothing of transport')
    ->expect('Lemonfiber\Sdk\Generated')
    ->not->toUse(['Saloon', 'Lemonfiber\Sdk\Http', 'GuzzleHttp', 'Psr\Http']);

arch('reading an envelope knows nothing of transport')
    ->expect('Lemonfiber\Sdk\Envelope')
    ->not->toUse(['Saloon', 'Lemonfiber\Sdk\Http', 'GuzzleHttp', 'Psr\Http']);

arch('live updates know nothing of transport')
    ->expect('Lemonfiber\Sdk\Events')
    ->not->toUse(['Saloon', 'Lemonfiber\Sdk\Http', 'GuzzleHttp', 'Psr\Http']);

arch('telling the time knows nothing of transport')
    ->expect('Lemonfiber\Sdk\Time')
    ->not->toUse(['Saloon', 'Lemonfiber\Sdk\Http', 'GuzzleHttp', 'Psr\Http']);

arch('every failure is one of ours')
    ->expect('Lemonfiber\Sdk\Exception')
    ->toImplement(Problem::class)
    ->ignoring([Problem::class, AnswerUnusable::class]);

arch('nothing outside the transport speaks to Saloon')
    ->expect('Saloon')
    ->toOnlyBeUsedIn(['Lemonfiber\Sdk\Http', Client::class, Admission::class, 'Lemonfiber\Sdk\Tests']);

arch()->preset()->security();
