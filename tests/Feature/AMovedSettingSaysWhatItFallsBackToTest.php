<?php

declare(strict_types=1);

use Lemonfiber\Sdk\Contract\Api;
use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Generated\SettingsEnvelope;
use Lemonfiber\Sdk\Tests\Support\VendoredDefinition;

/**
 * A settings answer whose change moved one setting to each default the contract tags.
 *
 * @return array<string, mixed>
 */
function aChangeMovingEachDefault(): array
{
    return [
        'rehearsed' => false,
        'secrets' => [],
        'settings' => [],
        'waiting' => [],
        'change' => [
            'agreement' => 'b3f1',
            'applied' => true,
            'regenerates' => [],
            'restarts' => ['sonarr'],
            'starts' => [],
            'waiting' => [],
            'settings' => [
                ['setting' => 'LEMONFIBER_BACKUP_LIMIT', 'value' => '4', 'was' => null, 'default' => ['of' => 'core', 'value' => 'off']],
                ['setting' => 'sonarr:port', 'value' => null, 'was' => '9000', 'default' => ['of' => 'declared', 'value' => '8989']],
                ['setting' => 'gluetun:countries', 'value' => 'Belgium', 'default' => ['of' => 'per-reader', 'readers' => [
                    ['service' => 'gluetun', 'variable' => 'SERVER_COUNTRIES', 'default' => 'Netherlands', 'met' => true],
                    ['service' => 'qbittorrent', 'variable' => 'VPN_COUNTRIES', 'met' => false],
                ]]],
                ['setting' => 'jellyfin:published', 'value' => null, 'default' => ['of' => 'none']],
            ],
        ],
    ];
}

it('types the default each moved setting falls back to by every tag the contract gives it, and requires it', function (): void {
    expect(VendoredDefinition::tags('settings', 'SettingDefault'))->toBe(['core', 'declared', 'per-reader', 'none'])
        ->and(VendoredDefinition::property('settings', 'SettingMove', 'default'))->toHaveKey('$ref')
        ->and(VendoredDefinition::requires('settings', 'SettingMove', 'default'))->toBeTrue()
        ->and(VendoredDefinition::requires('settings', 'ReaderDefault', 'default'))->toBeFalse();
});

it('reads the default of each moved setting by what it is of, keeping the own default of each reader and its absence', function (): void {
    $change = SettingsEnvelope::in(new Envelope(Api::VERSION, 'settings', aChangeMovingEachDefault()))->data['change'] ?? null;
    $said = [];

    foreach ($change['settings'] ?? [] as $move) {
        $default = $move['default'];
        $said[$move['setting']] = match ($default['of']) {
            'core', 'declared' => $default['of'] . ': ' . $default['value'],
            'per-reader' => array_map(
                static fn(array $reader): string => $reader['service'] . ' ' . ($reader['met'] ? 'met' : 'unmet') . ': ' . ($reader['default'] ?? 'declares none'),
                $default['readers'],
            ),
            'none' => 'none',
        };
    }

    expect($said)->toBe([
        'LEMONFIBER_BACKUP_LIMIT' => 'core: off',
        'sonarr:port' => 'declared: 8989',
        'gluetun:countries' => ['gluetun met: Netherlands', 'qbittorrent unmet: declares none'],
        'jellyfin:published' => 'none',
    ]);
});
