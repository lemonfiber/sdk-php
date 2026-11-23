<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Envelope;

/**
 * The wrapper every lemonfiber answer arrives in.
 *
 * `data` is typed by `kind` rather than left open. The generated
 * contract types supply `TData`; an `Envelope<mixed>` on a public surface means
 * the generation was not used.
 *
 * @template-covariant TData
 */
final readonly class Envelope
{
    /**
     * @param TData $data
     * @param string|null $host the machine this answer is about, where it is not the one lemonfiber runs on
     * @param string|null $job the work that said this, by the name its accepting reply gave it
     */
    public function __construct(
        public int $apiVersion,
        public string $kind,
        public mixed $data,
        public ?string $host = null,
        public ?string $job = null,
    ) {}
}
