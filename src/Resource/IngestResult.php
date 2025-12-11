<?php

declare(strict_types=1);

namespace Brd6\TinybirdSdk\Resource;

/**
 * Represents the result of a data ingestion operation.
 *
 * @see https://www.tinybird.co/docs/api-reference/datasource-api
 */
class IngestResult extends AbstractResource
{
    public int $successfulRows = 0;
    public int $quarantinedRows = 0;
    public ?string $importId = null;
    public ?string $datasource = null;
    public float $elapsedTime = 0.0;

    protected function initialize(): void
    {
        $data = $this->getRawData();

        $this->successfulRows = (int) ($data['successful_rows'] ?? 0);
        $this->quarantinedRows = (int) ($data['quarantined_rows'] ?? 0);
        $this->importId = $data['import_id'] ?? null;
        $this->datasource = $data['datasource'] ?? null;
        $this->elapsedTime = (float) ($data['elapsed_time'] ?? 0.0);
    }

    public function hasQuarantinedRows(): bool
    {
        return $this->quarantinedRows > 0;
    }

    public function getTotalRows(): int
    {
        return $this->successfulRows + $this->quarantinedRows;
    }
}
