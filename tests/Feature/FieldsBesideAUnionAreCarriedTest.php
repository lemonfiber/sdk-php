<?php

declare(strict_types=1);

/**
 * The branches of the union the generated source declares under a name, as its `@phpstan-type` line spells it.
 *
 * @return list<string>
 */
function branchesDeclared(string $file, string $name): array
{
    $source = (string) file_get_contents(__DIR__ . '/../../src/Generated/' . $file);

    return preg_match('/@phpstan-type ' . $name . ' (.+)$/m', $source, $found) === 1 ? explode('}|array{', $found[1]) : [];
}

it('carries the fields beside a union in every branch of it, and every branch', function (string $file, string $name, array $beside, array $branches): void {
    $declared = branchesDeclared($file, $name);

    expect($declared)->toHaveCount(count($branches));

    foreach ($branches as $at => $branch) {
        expect($declared[$at])->toContain($branch);

        foreach ($beside as $field) {
            expect($declared[$at])->toContain($field);
        }
    }
})->with([
    'the running copy' => ['CopyingEnvelope.php', 'RunningCopy', ['scope: CopiedScope', 'what: CopyWhat', 'part: string'], ["state: 'working'", "limit: int, state: 'paced'", "state: 'stalled', wrote: Instant"]],
    'a plugin image vouched for' => ['PluginProvenanceEnvelope.php', 'Vouch', ['digest: string', 'image: string', 'service: string'], ["by: string, provenance: 'signed'", "provenance: 'unproven', why: string", "provenance: 'refused', why: string"]],
    'what an image is built on' => ['PluginProvenanceEnvelope.php', 'Based', ['image: string', 'release: string'], ["basis: 'recorded', digests: list<string>", "basis: 'unproven', why: string", "basis: 'refused', why: string"]],
]);
