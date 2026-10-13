<?php

declare(strict_types=1);

use Lemonfiber\Sdk\Exception\ConfigurationProblem;
use Lemonfiber\Sdk\KeyMint;
use Lemonfiber\Sdk\MintProof;

it('spells the text a device key signs as the contract publishes it', function (): void {
    expect(KeyMint::of('den', 'member:anna', 'other')->signedText('c'))
        ->toBe("lemonfiber-mint/1\nc\nden\nmember:anna\nother\nkey\n")
        ->and(KeyMint::of('den', 'read', 'mcp', claim: true)->signedText('c'))
        ->toBe("lemonfiber-mint/1\nc\nden\nread\nmcp\nclaim\n");
});

it('refuses a part holding a line feed, which would sign another mint', function (string $name, string $scope, string $purpose): void {
    expect(static fn(): KeyMint => KeyMint::of($name, $scope, $purpose))->toThrow(ConfigurationProblem::class);
})->with([
    'name' => ["den\nread", 'read', 'other'],
    'scope' => ['den', "read\n", 'other'],
    'purpose' => ['den', 'read', "\nother"],
]);

it('refuses a challenge holding a line feed', function (): void {
    expect(static fn(): string => KeyMint::of('den', 'read', 'other')->signedText("c\nden"))->toThrow(ConfigurationProblem::class);
});

it('says nothing a proof carries', function (): void {
    expect(print_r(MintProof::password('the password'), true))->not->toContain('the password')
        ->and(print_r(MintProof::device('pixel', 'a-challenge', 'sig'), true))->not->toContain('a-challenge');
});
