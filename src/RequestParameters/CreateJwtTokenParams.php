<?php

declare(strict_types=1);

namespace Brd6\TinybirdSdk\RequestParameters;

/**
 * @see https://www.tinybird.co/docs/api-reference/token-api
 */
class CreateJwtTokenParams extends AbstractRequestParameters
{
    /**
     * @param string $name Token name
     * @param int $expirationTime Unix timestamp for expiration
     * @param array<array{type: string, resource?: string, fixed_params?: array<string, mixed>}> $scopes
     */
    public function __construct(
        public string $name,
        public int $expirationTime,
        public array $scopes = [],
    ) {
    }

    /**
     * @return array<string, string>
     */
    public function toQueryParams(): array
    {
        return [
            'name' => $this->name,
            'expiration_time' => (string) $this->expirationTime,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function toBody(): array
    {
        return ['scopes' => $this->scopes];
    }
}
