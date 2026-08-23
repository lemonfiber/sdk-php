<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Envelope;

/**
 * The wrapper every lemonfiber answer arrives in (ARCH-R46).
 *
 * `data` is typed by `kind` rather than left open (ARCH-R63). The generated
 * contract types supply `TData`; an `Envelope<mixed>` on a public surface means
 * the generation was not used.
 *
 * @template-covariant TData
 */
final readonly class Envelope
{
    /**
     * @param TData $data
     */
    public function __construct(
        public int $apiVersion,
        public string $kind,
        public mixed $data,
    ) {}
}
