<?php

declare(strict_types=1);

namespace Brd6\TinybirdSdk\Resource;

/**
 * @see https://www.tinybird.co/docs/api-reference/token-api
 */
class Token extends AbstractResource
{
    public string $name = '';
    public ?string $description = null;
    public ?string $token = null;

    /** @var array<TokenScope> */
    public array $scopes = [];

    protected function initialize(): void
    {
        $data = $this->getRawData();

        $this->name = (string) ($data['name'] ?? '');
        $this->description = $data['description'] ?? null;
        $this->token = $data['token'] ?? null;

        if (isset($data['scopes'])) {
            $this->scopes = array_map(
                static fn (array $scope) => TokenScope::fromArray($scope),
                (array) $data['scopes'],
            );
        }
    }

    public function hasScope(string $type): bool
    {
        foreach ($this->scopes as $scope) {
            if ($scope->type === $type) {
                return true;
            }
        }

        return false;
    }

    public function isAdmin(): bool
    {
        return $this->hasScope('ADMIN');
    }
}
