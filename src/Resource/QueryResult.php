<?php

declare(strict_types=1);

namespace Brd6\TinybirdSdk\Resource;

/**
 * @see https://www.tinybird.co/docs/api-reference/query-api
 * @see https://www.tinybird.co/docs/forward/work-with-data/publish-data/endpoints
 * @see https://clickhouse.com/docs/en/interfaces/formats/JSON
 */
class QueryResult extends AbstractResource
{
    /** @var array<array<string, mixed>> */
    public array $data = [];

    /** @var array<array<string, mixed>> */
    public array $meta = [];

    public int $rows = 0;
    public int $rowsBeforeLimitAtLeast = 0;

    /** @var array<string, mixed> */
    public array $statistics = [];

    protected function initialize(): void
    {
        $data = $this->getRawData();

        $this->data = (array) ($data['data'] ?? []);
        $this->meta = (array) ($data['meta'] ?? []);
        $this->rows = (int) ($data['rows'] ?? 0);
        $this->rowsBeforeLimitAtLeast = (int) ($data['rows_before_limit_at_least'] ?? 0);
        $this->statistics = (array) ($data['statistics'] ?? []);
    }

    public function getElapsedTime(): float
    {
        return (float) ($this->statistics['elapsed'] ?? 0.0);
    }

    public function getBytesRead(): int
    {
        return (int) ($this->statistics['bytes_read'] ?? 0);
    }

    public function getRowsRead(): int
    {
        return (int) ($this->statistics['rows_read'] ?? 0);
    }

    /**
     * @return array<string>
     */
    public function getColumnNames(): array
    {
        return array_map(
            static fn (array $col) => (string) ($col['name'] ?? ''),
            $this->meta,
        );
    }

    public function isEmpty(): bool
    {
        return $this->rows === 0;
    }
}
