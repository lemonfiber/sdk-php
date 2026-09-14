<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk;

use function count;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Exception\UnexpectedKind;
use Lemonfiber\Sdk\Generated\LogEnvelope;

/**
 * The lines that came back, and the window they came through.
 *
 * @phpstan-import-type Data from LogEnvelope
 *
 * **The answer says nothing about what it left out.** What arrives is so many
 * `log` envelopes and then the end of the body: no count of what lay behind
 * them, no mark where the gathering stopped, nothing a client could read to
 * learn that there was more. So the size that was asked for is carried here
 * beside the number that arrived, and that pairing is the whole of what can
 * honestly be said about the edge of the view.
 *
 * As many lines as were asked for means the view stops at the bound and what is
 * behind it is not something this answer knows. Fewer means the bound cut
 * nothing — a smaller claim than this being everything the service ever said,
 * since the engine keeps what it keeps and no more.
 *
 * The pairing is exact while one service is named, which is the arrangement
 * {@see Logs} allows and the second reason it allows only that one.
 */
final readonly class LogWindow
{
    /**
     * @param list<Envelope<Data>> $lines
     */
    private function __construct(private Logs $asked, private array $lines) {}

    /**
     * The window a body of one document a line came back as.
     *
     * Every line is held to the kind it must carry. The read renders `log` and
     * nothing else, so a line wearing another kind is an answer this client
     * cannot place rather than one to hand on untyped.
     *
     * @param list<Envelope<mixed>> $arrived
     *
     * @throws UnexpectedKind
     */
    public static function of(Logs $asked, array $arrived): self
    {
        $lines = [];

        foreach ($arrived as $envelope) {
            $lines[] = LogEnvelope::in($envelope);
        }

        return new self($asked, $lines);
    }

    /**
     * The service this window is over.
     */
    public function service(): string
    {
        return $this->asked->service();
    }

    /**
     * How many lines were asked for.
     */
    public function bound(): int
    {
        return $this->asked->bound();
    }

    /**
     * The lines, each in the envelope it arrived in, oldest first.
     *
     * @return list<Envelope<Data>>
     */
    public function lines(): array
    {
        return $this->lines;
    }

    /**
     * How many lines arrived.
     */
    public function count(): int
    {
        return count($this->lines);
    }

    /**
     * Whether the view stops where it was told to stop.
     *
     * True where as many lines came back as were asked for: the bound is the
     * edge of what is shown, and what lies behind it is not carried on the
     * wire and is not guessed at here. False where fewer came back, which says
     * the bound cut nothing and says nothing further.
     */
    public function reachedTheBound(): bool
    {
        return $this->count() === $this->bound();
    }
}
