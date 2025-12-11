<?php

declare(strict_types=1);

namespace Brd6\TinybirdSdk\Resource;

/**
 * @see https://www.tinybird.co/docs/api-reference/token-api
 */
class TokenScope extends AbstractResource
{
    public string $type = '';
    public ?string $resource = null;
    public ?string $filter = null;

    /** @var array<string, mixed>|null */
    public ?array $fixedParams = null;

    protected function initialize(): void
    {
        $data = $this->getRawData();

        $this->type = (string) ($data['type'] ?? '');
        $this->resource = $data['resource'] ?? null;
        $this->filter = $data['filter'] ?? null;
        $this->fixedParams = $data['fixed_params'] ?? null;
    }
}
