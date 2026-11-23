<?php

// Generated from the contract vendored under contract/. Do not edit.
// Contract::SOURCE names its revision and Contract::API_VERSION its wire version.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\Payload;
use Lemonfiber\Sdk\Exception\UnexpectedKind;

/**
 * The `walkthrough` envelope, shaped as the contract describes it.
 *
 * @phpstan-import-type Line from Shapes
 * @phpstan-import-type Reason from Shapes
 * @phpstan-import-type Shape from Shapes
 * @phpstan-import-type WalkthroughStep from Shapes
 * @phpstan-type Handover array{next: list<Next>}
 * @phpstan-type Link 'hardlinked'|'copied'
 * @phpstan-type Next 'more-content'|'household'|'client-apps'
 * @phpstan-type Stopped array{logs: list<string>, reason: Reason, remedy: string, step: WalkthroughStep}
 * @phpstan-type WalkthroughReport array{already_here: bool, handover?: Handover|null, in_background: bool, item?: string|null, lines: list<Line>, link?: Link|null, proves: string, shape: Shape, state: WalkthroughState, stopped?: Stopped|null, suggestions: list<string>}
 * @phpstan-type WalkthroughState 'offered'|'skipped'|'searching'|'grabbing'|'downloading'|'importing'|'complete'|'failed'|'abandoned'
 * @phpstan-type Data WalkthroughReport
 */
final class WalkthroughEnvelope
{
    /**
     * The kind an envelope must carry to be read as this one.
     */
    public const Kind KIND = Kind::Walkthrough;

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

        return new Envelope($envelope->apiVersion, $envelope->kind, $data, $envelope->host, $envelope->job);
    }
}
