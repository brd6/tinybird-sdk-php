<?php

declare(strict_types=1);

namespace Brd6\TinybirdSdk\Resource;

use DateTimeImmutable;

use function array_map;

/**
 * @see https://www.tinybird.co/docs/api-reference/datasource-api
 */
class DataSource extends AbstractResource
{
    public string $id = '';
    public string $name = '';
    public string $cluster = '';
    public string $description = '';
    public string $type = '';
    public bool $replicated = false;
    public int $version = 0;
    public ?string $project = null;
    public int $quarantineRows = 0;
    public ?DateTimeImmutable $createdAt = null;
    public ?DateTimeImmutable $updatedAt = null;

    /** @var array<string, mixed> */
    public array $tags = [];

    /** @var array<string, mixed> */
    public array $headers = [];

    /** @var array<string> */
    public array $sharedWith = [];

    public ?Engine $engine = null;

    /** @var array<Column> */
    public array $columns = [];

    public ?Statistics $statistics = null;

    /** @var array<UsedBy> */
    public array $usedBy = [];

    /** @var array<string, mixed> */
    public array $newColumnsDetected = [];

    protected function initialize(): void
    {
        $data = $this->getRawData();

        $this->id = (string) ($data['id'] ?? '');
        $this->name = (string) ($data['name'] ?? '');
        $this->cluster = (string) ($data['cluster'] ?? '');
        $this->description = (string) ($data['description'] ?? '');
        $this->type = (string) ($data['type'] ?? '');
        $this->replicated = (bool) ($data['replicated'] ?? false);
        $this->version = (int) ($data['version'] ?? 0);
        $this->project = $data['project'] ?? null;
        $this->quarantineRows = (int) ($data['quarantine_rows'] ?? 0);
        $this->tags = (array) ($data['tags'] ?? []);
        $this->headers = (array) ($data['headers'] ?? []);
        $this->sharedWith = (array) ($data['shared_with'] ?? []);
        $this->newColumnsDetected = (array) ($data['new_columns_detected'] ?? []);

        if (isset($data['created_at'])) {
            $this->createdAt = new DateTimeImmutable((string) $data['created_at']);
        }

        if (isset($data['updated_at'])) {
            $this->updatedAt = new DateTimeImmutable((string) $data['updated_at']);
        }

        if (isset($data['engine']) && is_array($data['engine'])) {
            $this->engine = Engine::fromArray($data['engine']);
        }

        if (isset($data['statistics']) && is_array($data['statistics'])) {
            $this->statistics = Statistics::fromArray($data['statistics']);
        }

        $this->columns = array_map(
            static fn (array $column) => Column::fromArray($column),
            (array) ($data['columns'] ?? []),
        );

        $this->usedBy = array_map(
            static fn (array $ref) => UsedBy::fromArray($ref),
            (array) ($data['used_by'] ?? []),
        );
    }

    public function getColumn(string $name): ?Column
    {
        foreach ($this->columns as $column) {
            if ($column->name === $name) {
                return $column;
            }
        }

        return null;
    }

    public function hasQuarantinedRows(): bool
    {
        return $this->quarantineRows > 0;
    }

    public function isShared(): bool
    {
        return count($this->sharedWith) > 0;
    }

    public function getRowCount(): ?int
    {
        return $this->statistics?->rowCount;
    }

    public function getBytes(): ?int
    {
        return $this->statistics?->bytes;
    }
}
