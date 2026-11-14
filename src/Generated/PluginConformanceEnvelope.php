<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: c4e98c06e6a6aab2127ae392414bfb9f088b038d, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\Payload;
use Lemonfiber\Sdk\Exception\UnexpectedKind;

/**
 * The `plugin-conformance` envelope, shaped as the contract describes it.
 *
 * @phpstan-import-type Violation from Shapes
 * @phpstan-type Conformed array{cases: list<Judged>, conforms: bool, id: string, refusals: list<Violation>}
 * @phpstan-type Judged array{case: string, contract: string, recording: string, verdict: Verdict}
 * @phpstan-type Verdict array{outcome: 'passed'}|array{faults: list<string>, outcome: 'failed'}|array{outcome: 'unrecorded'}|array{outcome: 'unproven', why: string}
 * @phpstan-type Data Conformed
 */
final class PluginConformanceEnvelope
{
    /**
     * The kind an envelope must carry to be read as this one.
     */
    public const Kind KIND = Kind::PluginConformance;

    /**
     * The same envelope with its payload typed by its kind.
     *
     * @param  Envelope<mixed>  $envelope
     * @return Envelope<Data>
     *
     * @throws UnexpectedKind
     */
    public static function in(Envelope $envelope): Envelope
    {
        /** @var Data $data */
        $data = Payload::under(self::KIND, $envelope);

        return new Envelope($envelope->apiVersion, $envelope->kind, $data, $envelope->host);
    }
}
