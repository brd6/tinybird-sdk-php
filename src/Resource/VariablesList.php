<?php

declare(strict_types=1);

namespace Brd6\TinybirdSdk\Resource;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * @implements IteratorAggregate<int, Variable>
 * @see https://www.tinybird.co/docs/api-reference/environment-variables-api
 */
class VariablesList extends AbstractResource implements IteratorAggregate, Countable
{
    /** @var array<Variable> */
    private array $variables = [];

    protected function initialize(): void
    {
        $data = $this->getRawData();

        $this->variables = array_map(
            static fn (array $var) => Variable::fromArray($var),
            (array) ($data['variables'] ?? []),
        );
    }

    /**
     * @return array<Variable>
     */
    public function getVariables(): array
    {
        return $this->variables;
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->variables);
    }

    public function count(): int
    {
        return count($this->variables);
    }

    public function isEmpty(): bool
    {
        return $this->count() === 0;
    }
}
