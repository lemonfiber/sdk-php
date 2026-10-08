<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Scripts;

use function array_filter;
use function array_map;
use function count;
use function implode;
use function in_array;
use function is_array;
use function is_bool;
use function is_string;
use function json_encode;
use function rtrim;
use function sprintf;
use function trim;

/**
 * The PHP source one action is written as, after the header every generated file opens with.
 *
 * A class per action: a constructor taking each argument by name, with the
 * type and default the contract gives it and the arguments it gives no default
 * first; the arguments carrying the operator's yes named; and `rehearsed()`
 * only where the action takes `dry_run`.
 *
 * @phpstan-import-type Argument from ActionArgument
 * @phpstan-import-type Action from Actions
 */
final readonly class ActionSource
{
    public function __construct(private SchemaTypes $types) {}

    /**
     * @param  Action  $action
     */
    public function written(string $class, array $action): string
    {
        $arguments = $this->ordered($action['arguments']);

        return sprintf(
            <<<'PHP'

                use Lemonfiber\Sdk\ActionRequest;

                /**
                 * The `%s` action, as the contract lists it.%s
                 */
                final class %s extends ActionRequest
                {
                    /**
                     * The action's name, as the command line spells it.
                     */
                    public const string ACTION = %s;
                %s
                    public function action(): string
                    {
                        return self::ACTION;
                    }

                    /**
                     * @return list<string>
                     */
                    public function consent(): array
                    {
                        return %s;
                    }
                %s
                    /**
                     * @return array<string, mixed>
                     */
                    protected function arguments(): array
                    {
                        return [%s];
                    }
                }

                PHP,
            $action['action'],
            $this->consentNote($action['consent']),
            $class,
            $this->types->quoted($action['action']),
            $this->constructor($arguments, $action['consent']),
            $this->literal($action['consent']),
            $action['rehearsal'] ? $this->rehearsal() : '',
            $this->body($action['arguments']),
        );
    }

    /**
     * The arguments a caller must pass, then those with a default, each in the contract's order.
     *
     * @param  list<Argument>  $arguments
     * @return list<Argument>
     */
    private function ordered(array $arguments): array
    {
        return [
            ...array_filter($arguments, static fn(array $argument): bool => ! $argument['optional']),
            ...array_filter($arguments, static fn(array $argument): bool => $argument['optional']),
        ];
    }

    /**
     * @param  list<string>  $consent
     */
    private function consentNote(array $consent): string
    {
        if ($consent === []) {
            return '';
        }

        return sprintf(
            "\n *\n * `%s` %s the operator's yes to what it would cost.",
            implode('`, `', $consent),
            count($consent) === 1 ? 'carries' : 'carry',
        );
    }

    /**
     * @param  list<Argument>  $arguments
     * @param  list<string>  $consent
     */
    private function constructor(array $arguments, array $consent): string
    {
        if ($arguments === []) {
            return '';
        }

        $docs = '';
        $parameters = '';

        foreach ($arguments as $argument) {
            $said = in_array($argument['wire'], $consent, true) ? trim("Carries the operator's yes. " . $argument['description']) : $argument['description'];
            $docs .= rtrim(sprintf('     * @param  %s  $%s  %s', $argument['doc'], $argument['param'], $said)) . "\n";
            $parameters .= sprintf(
                "        public readonly %s $%s%s,\n",
                $argument['native'],
                $argument['param'],
                $argument['optional'] ? ' = ' . $this->literal($argument['default']) : '',
            );
        }

        return sprintf("\n    /**\n%s     */\n    public function __construct(\n%s    ) {}\n", $docs, $parameters);
    }

    private function rehearsal(): string
    {
        return <<<'PHP'

                /**
                 * The same request, asking what it would do rather than doing it.
                 */
                public function rehearsed(): static
                {
                    return $this->asRehearsal();
                }

            PHP;
    }

    /**
     * @param  list<Argument>  $arguments
     */
    private function body(array $arguments): string
    {
        if ($arguments === []) {
            return '';
        }

        $entries = array_map(
            fn(array $argument): string => sprintf("            %s => \$this->%s,\n", $this->types->quoted($argument['wire']), $argument['param']),
            $arguments,
        );

        return "\n" . implode('', $entries) . '        ';
    }

    private function literal(mixed $value): string
    {
        return match (true) {
            is_array($value) => '[' . implode(', ', array_map($this->literal(...), $value)) . ']',
            $value === null => 'null',
            is_bool($value) => $value ? 'true' : 'false',
            is_string($value) => $this->types->quoted($value),
            default => json_encode($value, JSON_THROW_ON_ERROR),
        };
    }
}
