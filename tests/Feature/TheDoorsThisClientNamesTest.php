<?php

declare(strict_types=1);

/**
 * The gate holding this client's paths to the contract page, shown refusing.
 *
 * A gate nobody has watched fail is a gate nobody knows the shape of. Both
 * defects it exists to catch are staged here against fixtures: a read the page
 * names that no constant reaches, and a constant naming a door the page does
 * not. The real tree passing is asserted last, so a fixture that stopped
 * resembling the real one would not read as agreement.
 */
const GATE = __DIR__ . '/../../scripts/the_doors_this_client_names.py';

/** A spec checkout carrying nothing but the page, with the given reads fenced under it. */
function aPageNaming(string $root, string ...$reads): string
{
    $page = $root . '/20-architecture/contracts/web-api.md';
    mkdir(dirname($page), 0o755, true);
    $fenced = implode("\n", array_map(static fn(string $read): string => 'GET ' . $read, $reads));
    file_put_contents($page, "# The web API\n\n## Reading\n\n```\n{$fenced}\n```\n\n## Acting\n\nElsewhere.\n");

    return $root;
}

/** A client tree carrying nothing but the contract class, holding the given paths. */
function aClientHolding(string $root, string ...$paths): string
{
    $class = $root . '/src/Contract/Api.php';
    mkdir(dirname($class), 0o755, true);
    $held = '';
    foreach ($paths as $index => $path) {
        $held .= "    public const string E{$index}_ENDPOINT = '{$path}';\n";
    }
    file_put_contents($class, "<?php\n\nfinal class Api\n{\n{$held}}\n");

    return $root;
}

/**
 * What the gate said, and whether it refused.
 *
 * Run with the shell rather than through a process library, which would be a
 * dependency this package carries for four assertions. What it says travels on
 * both streams, so both are read.
 *
 * @return array{int, string}
 */
function theGateOn(string $repo, string $spec): array
{
    $command = sprintf(
        'python3 %s --repo %s --spec %s 2>&1',
        escapeshellarg(GATE),
        escapeshellarg($repo),
        escapeshellarg($spec),
    );

    $said = [];
    $code = 0;
    exec($command, $said, $code);

    return [$code, implode("\n", $said)];
}

/**
 * Enough reads to clear the floor the gate holds its own reading to.
 *
 * @return list<string>
 */
function enoughReads(): array
{
    $reads = [];
    for ($n = 1; $n <= 30; $n++) {
        $reads[] = "/api/r{$n}";
    }

    return $reads;
}

/** A spec checkout beside this one, wherever it is, or nothing. */
function theSpecBeside(): ?string
{
    foreach ([__DIR__ . '/../../.spec-canonical', __DIR__ . '/../../../spec'] as $where) {
        if (is_dir($where)) {
            return $where;
        }
    }

    return null;
}

it('Q-R66 — refuses a read the contract names that this client cannot reach', function (): void {
    $reads = enoughReads();
    $spec = aPageNaming(sys_get_temp_dir() . '/' . uniqid('spec', true), ...$reads);
    // Every read but the last, which is the defect: the page offers it and no
    // constant holds a path for it, so a caller has no way to ask.
    $repo = aClientHolding(sys_get_temp_dir() . '/' . uniqid('repo', true), ...array_slice($reads, 0, -1));

    [$code, $said] = theGateOn($repo, $spec);

    expect($code)->toBe(1)
        ->and($said)->toContain('this client holds no path for them')
        ->and($said)->toContain('/api/r30');
});

it('Q-R66 — refuses a door this client holds that the contract page does not name', function (): void {
    $reads = enoughReads();
    $spec = aPageNaming(sys_get_temp_dir() . '/' . uniqid('spec', true), ...$reads);
    $repo = aClientHolding(sys_get_temp_dir() . '/' . uniqid('repo', true), ...$reads, ...['/api/invented']);

    [$code, $said] = theGateOn($repo, $spec);

    expect($code)->toBe(1)
        ->and($said)->toContain('the contract page does not name')
        ->and($said)->toContain('/api/invented');
});

it('Q-R66 — refuses a reading that found too little to be reading the page at all', function (): void {
    // The failure a sweep has when it matches nothing: agreement, reported
    // confidently, about a page it never read. A floor under the reading is
    // what tells that apart from a surface that genuinely shrank.
    $spec = aPageNaming(sys_get_temp_dir() . '/' . uniqid('spec', true), '/api/status');
    $repo = aClientHolding(sys_get_temp_dir() . '/' . uniqid('repo', true), '/api/status');

    [$code, $said] = theGateOn($repo, $spec);

    expect($code)->toBe(1)
        ->and($said)->toContain('no longer reading it');
});

it('names a path for every read the contract page names', function (): void {
    // The real tree against the real page. Skipped where the spec is not
    // beside this checkout: CI puts it there, and a developer who has not
    // cloned it is told rather than failed.
    [$code, $said] = theGateOn(__DIR__ . '/../..', (string) theSpecBeside());

    expect($said)->toContain('holds a path for each')
        ->and($code)->toBe(0);
})->skip(! is_string(theSpecBeside()), 'no spec checkout beside this one');
