<?php

declare(strict_types=1);

namespace Brd6\TinybirdSdk\Resource;

use ArrayIterator;
use Countable;
use IteratorAggregate;

/**
 * @implements IteratorAggregate<int, Token>
 *
 * @see https://www.tinybird.co/docs/api-reference/token-api
 */
class TokensList extends AbstractResource implements IteratorAggregate, Countable
{
    /** @var array<Token> */
    public array $tokens = [];

    protected function initialize(): void
    {
        $data = $this->getRawData();

        if (isset($data['tokens'])) {
            $this->tokens = array_map(
                static fn (array $token) => Token::fromArray($token),
                (array) $data['tokens'],
            );
        }
    }

    /**
     * @return ArrayIterator<int, Token>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->tokens);
    }

    public function count(): int
    {
        return count($this->tokens);
    }

    public function findByName(string $name): ?Token
    {
        foreach ($this->tokens as $token) {
            if ($token->name === $name) {
                return $token;
            }
        }

        return null;
    }
}
