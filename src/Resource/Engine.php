<?php

declare(strict_types=1);

namespace Brd6\TinybirdSdk\Resource;

class Engine extends AbstractResource
{
    public string $engine = '';
    public string $engineSortingKey = '';
    public string $enginePartitionKey = '';
    public string $enginePrimaryKey = '';

    protected function initialize(): void
    {
        $data = $this->getRawData();

        $this->engine = (string) ($data['engine'] ?? '');
        $this->engineSortingKey = (string) ($data['engine_sorting_key'] ?? '');
        $this->enginePartitionKey = (string) ($data['engine_partition_key'] ?? '');
        $this->enginePrimaryKey = (string) ($data['engine_primary_key'] ?? '');
    }
}
