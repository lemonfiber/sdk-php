<?php

// Generated from the contract vendored under contract/. Do not edit.
// Contract::SOURCE names its revision and Contract::API_VERSION its wire version.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

/**
 * What a member's surface may not show, as the contract publishes it.
 */
final class HouseholdWords
{
    /**
     * Every service, engine and tunnel named, the bundle's parts with hyphens read as spaces.
     *
     * @var list<string>
     */
    public const array NAMES = [
        'apprise',
        'audiobookshelf',
        'bazarr',
        'bindery',
        'byparr',
        'caddy',
        'calibre',
        'calibre web automated',
        'docker',
        'emby',
        'flaresolverr',
        'gluetun',
        'gotify',
        'homepage',
        'jellyfin',
        'jellyseerr',
        'komga',
        'lemonfiber',
        'lidarr',
        'medusa',
        'navidrome',
        'ntfy',
        'nzbget',
        'nzbhydra',
        'nzbhydra2',
        'overseerr',
        'plex',
        'podman',
        'prowlarr',
        'qbittorrent',
        'radarr',
        'readarr',
        'recyclarr',
        'sabnzbd',
        'seerr',
        'sickchill',
        'sickgear',
        'sonarr',
        'tdarr',
        'transmission',
        'unpackerr',
        'whisparr',
        'wireguard',
    ];

    /**
     * Every term for how the stack is built and run.
     *
     * @var list<string>
     */
    public const array TERMS = [
        'administrator',
        'admin',
        'api',
        'backbone',
        'bitrate',
        'block account',
        'byte',
        'bytes',
        'certificate',
        'codec',
        'compose',
        'config',
        'configuration',
        'container',
        'containers',
        'curator',
        'custom format',
        'dashboard',
        'database',
        'disk',
        'dns',
        'download client',
        'downloaded',
        'downloader',
        'downloading',
        'endpoint',
        'engine',
        'error',
        'gigabyte',
        'grab',
        'grabbed',
        'hardlink',
        'hdr',
        'http',
        'https',
        'id',
        'identifier',
        'image',
        'import',
        'imported',
        'indexer',
        'indexers',
        'ip',
        'json',
        'killswitch',
        'log',
        'logs',
        'media server',
        'megabyte',
        'monitored',
        'nzb',
        'operator',
        'peer',
        'pem',
        'pipeline',
        'plugin',
        'port',
        'port forwarding',
        'proxy',
        'quality',
        'quality profile',
        'queue',
        'queued',
        'quick connect',
        'ratio',
        'remux',
        'request service',
        'retention',
        'root folder',
        'seed',
        'seeding',
        'server',
        'servers',
        'service',
        'services',
        'stack',
        'stalled',
        'storage',
        'terabyte',
        'token',
        'torrent',
        'tracker',
        'transcode',
        'transcoding',
        'tunnel',
        'upstream',
        'url',
        'usenet',
        'vpn',
        'webhook',
    ];

    /**
     * Every unit a size or a rate is written in.
     *
     * @var list<string>
     */
    public const array SIZES = [
        'b',
        'kb',
        'mb',
        'gb',
        'tb',
        'kib',
        'mib',
        'gib',
        'tib',
        'kbps',
        'mbps',
        'gbps',
    ];

    /**
     * Every mark that shows an address or a rate wherever it is written.
     *
     * @var list<string>
     */
    public const array MARKS = [
        '://',
        'localhost',
        'www.',
        '/s',
        'per second',
    ];

    /**
     * Every name lemonfiber gives its own things, each with what it names, rendered in every locale as declared.
     *
     * @var array<string, string>
     */
    public const array DECLARED = [
        'form' => 'names a combination of profiles a stack declares, and an argument to the verbs that act on one',
        'stack' => 'names the machine in every command, every path and every page of the documentation',
    ];

    /**
     * Texts a version, an address, a size or a rate makes unfit, each with what in it a member may not be shown.
     *
     * @var list<array{text: string, names: string}>
     */
    public const array EXAMPLES = [
        ['text' => 'It needs 4 GB more', 'names' => '4'],
        ['text' => 'It needs 12GB more', 'names' => '12gb'],
        ['text' => 'Fetching at 3 MB/s', 'names' => '3'],
        ['text' => 'Fetching forty per second', 'names' => 'per second'],
        ['text' => 'Reached at 192.168.1.20', 'names' => '192.168.1.20'],
        ['text' => 'Open it at localhost', 'names' => 'localhost'],
        ['text' => 'See www.house.example', 'names' => 'www.'],
        ['text' => 'Open it at house://shelf', 'names' => '://'],
        ['text' => 'Now on v1.4.2', 'names' => 'v1.4.2'],
    ];
}
