<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk;

use Lemonfiber\Sdk\Exception\ConfigurationProblem;

use function trim;

/**
 * A look at what one service has been saying, of a size somebody chose.
 *
 * A read that ends. lemonfiber gathers the scrollback, renders a `log` envelope
 * per line and closes, so what comes back is a window rather than a terminal
 * somebody has to be shown how to close. {@see LogWindow} is what it comes back
 * as.
 *
 * **One service, said in the signature.** lemonfiber takes `service` more than
 * once and takes `form` beside it, and this takes neither. The count is applied
 * per service — a scrollback is the tail that was asked for, times the services
 * it was asked about — so a window naming two is a window twice the size of the
 * number written on it, and the size is the one thing it exists to state.
 *
 * **How many lines is the caller's, every time.** There is no default here. A
 * number of lines is a decision about somebody's screen and this client owns
 * nobody's screen, so it is said at the call site or the call does not compile.
 * lemonfiber holds a ceiling on it and names that ceiling in the refusal; the
 * figure is not copied here, where it would go stale with nothing to notice.
 *
 * **`follow` is absent rather than offered and declined.** Asking lemonfiber to
 * keep reading is not this request with a flag on it. The answer stops being
 * lines and becomes a name for work that will not end, with the lines arriving
 * on the event stream instead — a different shape, read through
 * {@see Client::whatBecameOf()} and ended through {@see Client::letGoOf()}
 * rather than by the answer running out. One call that hands back either is a
 * call every caller has to branch on before it can read what it got.
 */
final readonly class Logs
{
    /**
     * The parameter naming the service to read.
     */
    private const string SERVICE = 'service';

    /**
     * The parameter saying how many existing lines to begin with.
     */
    private const string TAIL = 'tail';

    /**
     * The fewest lines a window can be cut to and still be a window.
     */
    private const int AT_LEAST = 1;

    private function __construct(private string $service, private int $lines) {}

    /**
     * The last so many lines of one service.
     *
     * Both are required of the signature rather than checked for afterwards.
     * A window over no service at all is the whole stack talking at once, which
     * is what a terminal is for; a window of no lines is a read that asked for
     * nothing and has nothing to show for it.
     *
     * @throws ConfigurationProblem
     */
    public static function ofService(string $service, int $lines): self
    {
        if (trim($service) === '') {
            throw ConfigurationProblem::logsNameNoService();
        }

        if ($lines < self::AT_LEAST) {
            throw ConfigurationProblem::windowHoldsNoLines($lines);
        }

        return new self($service, $lines);
    }

    /**
     * The service this window is over.
     */
    public function service(): string
    {
        return $this->service;
    }

    /**
     * How many lines were asked for.
     */
    public function bound(): int
    {
        return $this->lines;
    }

    /**
     * What this asks the read for.
     *
     * @return array<string, scalar|null>
     */
    public function parameters(): array
    {
        return [
            self::SERVICE => $this->service,
            self::TAIL => $this->lines,
        ];
    }
}
