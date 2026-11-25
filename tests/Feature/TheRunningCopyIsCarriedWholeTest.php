<?php

declare(strict_types=1);

/**
 * The type the generated source declares under a name, as its `@phpstan-type` line spells it.
 */
function declaredType(string $file, string $name): string
{
    $source = (string) file_get_contents(__DIR__ . '/../../src/Generated/' . $file);

    return preg_match('/@phpstan-type ' . $name . ' (.+)$/m', $source, $found) === 1 ? $found[1] : '';
}

it('carries the running copy\'s scope beside each state it can be in', function (): void {
    $branches = explode('}|array{', declaredType('CopyingEnvelope.php', 'RunningCopy'));

    expect($branches)->toHaveCount(3);

    foreach (['working', 'paced', 'stalled'] as $at => $state) {
        expect($branches[$at])->toContain('scope: CopiedScope')
            ->and($branches[$at])->toContain("state: '{$state}'")
            ->and($branches[$at])->toContain('what: CopyWhat');
    }

    expect($branches[1])->toContain('limit: int')
        ->and($branches[2])->toContain('wrote: Instant');
});
