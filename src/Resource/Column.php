<?php

declare(strict_types=1);

namespace Brd6\TinybirdSdk\Resource;

/**
 * Represents a column in a Data Source schema.
 *
 * @see https://www.tinybird.co/docs/api-reference/datasource-api
 */
class Column extends AbstractResource
{
    public string $name = '';
    public string $type = '';
    public ?string $normalizedName = null;
    public ?string $codec = null;
    public ?string $defaultValue = null;
    public bool $nullable = false;
    public ?string $jsonPath = null;

    protected function initialize(): void
    {
        $data = $this->getRawData();

        $this->name = (string) ($data['name'] ?? '');
        $this->type = (string) ($data['type'] ?? '');
        $this->normalizedName = $data['normalized_name'] ?? null;
        $this->codec = $data['codec'] ?? null;
        $this->defaultValue = $data['default_value'] ?? null;
        $this->nullable = (bool) ($data['nullable'] ?? false);
        $this->jsonPath = $data['jsonpath'] ?? null;
    }

    public function isNullable(): bool
    {
        return $this->nullable || str_starts_with($this->type, 'Nullable(');
    }
}
