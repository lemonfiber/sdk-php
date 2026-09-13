<?php

declare(strict_types=1);

use Lemonfiber\Sdk\Exception\ConfigurationProblem;
use Lemonfiber\Sdk\Http\CertificatePin;

it('takes a certificate digest as pairing material carries it', function (): void {
    expect(CertificatePin::fromSha256('86b25c676b761e9a398081373fec783c2bec970baa255370838aebb5c687841e')->toString())
        ->toBe('86b25c676b761e9a398081373fec783c2bec970baa255370838aebb5c687841e');
});

it('reads a digest written in capitals or padded with spaces', function (string $given): void {
    expect(CertificatePin::fromSha256($given)->toString())
        ->toBe('86b25c676b761e9a398081373fec783c2bec970baa255370838aebb5c687841e');
})->with([
    'capitals' => ['86B25C676B761E9A398081373FEC783C2BEC970BAA255370838AEBB5C687841E'],
    'spaces around it' => ["  86b25c676b761e9a398081373fec783c2bec970baa255370838aebb5c687841e\n"],
    'both' => [" 86B25C676B761E9A398081373FEC783C2BEC970BAA255370838AEBB5C687841E "],
]);

// The digest of the public key is the same size and a different value, and is what
// the option most reached for takes. Refusing it here is what keeps the two apart.
it('refuses the digest of the public key', function (): void {
    expect(fn(): CertificatePin => CertificatePin::fromSha256('l3ehbj5GiKLuq2UcJ7emYqINC4e/QvB6cHaSh/UDPQQ='))
        ->toThrow(ConfigurationProblem::class, 'digest of the certificate\'s public key');
});

it('refuses anything that is not 64 hexadecimal characters', function (string $given): void {
    expect(fn(): CertificatePin => CertificatePin::fromSha256($given))
        ->toThrow(ConfigurationProblem::class, '64 hexadecimal characters');
})->with([
    'nothing at all' => [''],
    'one short' => ['86b25c676b761e9a398081373fec783c2bec970baa255370838aebb5c687841'],
    'one too many' => ['86b25c676b761e9a398081373fec783c2bec970baa255370838aebb5c687841ef'],
    'a digest of another size' => ['da39a3ee5e6b4b0d3255bfef95601890afd80709'],
    'not hexadecimal' => ['86b25c676b761e9a398081373fec783c2bec970baa255370838aebb5c68784zz'],
    'colons between the pairs' => ['86:b2:5c:67:6b:76:1e:9a:39:80:81:37:3f:ec:78:3c:2b:ec:97:0b'],
    'a space in the middle' => ['86b25c676b761e9a398081373fec783c 2bec970baa255370838aebb5c687841e'],
]);
