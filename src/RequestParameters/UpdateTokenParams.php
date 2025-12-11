<?php

declare(strict_types=1);

namespace Brd6\TinybirdSdk\RequestParameters;

use function implode;
use function rawurlencode;

/**
 * @see https://www.tinybird.co/docs/api-reference/token-api
 */
class UpdateTokenParams extends AbstractRequestParameters
{
    /**
     * @param string|null $name New name (optional)
     * @param array<string>|null $scopes New scopes - overrides existing (optional)
     * @param string|null $description New description (optional)
     */
    public function __construct(
        public ?string $name = null,
        public ?array $scopes = null,
        public ?string $description = null,
    ) {
    }

    /**
     * Build form-urlencoded string with repeated scope params.
     * API expects: name=x&scope=A&scope=B (not scope[0]=A&scope[1]=B)
     */
    public function toFormString(): string
    {
        $parts = [];

        if ($this->name !== null) {
            $parts[] = 'name=' . rawurlencode($this->name);
        }

        if ($this->scopes !== null) {
            foreach ($this->scopes as $scope) {
                $parts[] = 'scope=' . rawurlencode($scope);
            }
        }

        if ($this->description !== null) {
            $parts[] = 'description=' . rawurlencode($this->description);
        }

        return implode('&', $parts);
    }
}
