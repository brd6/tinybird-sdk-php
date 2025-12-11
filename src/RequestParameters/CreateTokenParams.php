<?php

declare(strict_types=1);

namespace Brd6\TinybirdSdk\RequestParameters;

use function implode;
use function rawurlencode;

/**
 * @see https://www.tinybird.co/docs/api-reference/token-api
 */
class CreateTokenParams extends AbstractRequestParameters
{
    /**
     * @param string $name Token name
     * @param array<string> $scopes Scopes in format "SCOPE:TYPE[:resource][:filter]"
     * @param string|null $description Optional markdown description
     */
    public function __construct(
        public string $name,
        public array $scopes = [],
        public ?string $description = null,
    ) {
    }

    /**
     * Build form-urlencoded string with repeated scope params.
     * API expects: name=x&scope=A&scope=B (not scope[0]=A&scope[1]=B)
     */
    public function toFormString(): string
    {
        $parts = ['name=' . rawurlencode($this->name)];

        foreach ($this->scopes as $scope) {
            $parts[] = 'scope=' . rawurlencode($scope);
        }

        if ($this->description !== null) {
            $parts[] = 'description=' . rawurlencode($this->description);
        }

        return implode('&', $parts);
    }
}
