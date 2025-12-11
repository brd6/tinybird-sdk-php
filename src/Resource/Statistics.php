<?php

declare(strict_types=1);

namespace Brd6\TinybirdSdk\Resource;

class Statistics extends AbstractResource
{
    public ?int $bytes = null;
    public ?int $rowCount = null;

    protected function initialize(): void
    {
        $data = $this->getRawData();

        $this->bytes = isset($data['bytes']) ? (int) $data['bytes'] : null;
        $this->rowCount = isset($data['row_count']) ? (int) $data['row_count'] : null;
    }
}
