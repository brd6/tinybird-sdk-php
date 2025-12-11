<?php

declare(strict_types=1);

namespace Brd6\TinybirdSdk\Resource;

class AnalyzedColumn extends AbstractResource
{
    public string $name = '';
    public string $path = '';
    public string $recommendedType = '';
    public float $presentPct = 0.0;

    protected function initialize(): void
    {
        $data = $this->getRawData();

        $this->name = (string) ($data['name'] ?? '');
        $this->path = (string) ($data['path'] ?? '');
        $this->recommendedType = (string) ($data['recommended_type'] ?? '');
        $this->presentPct = (float) ($data['present_pct'] ?? 0.0);
    }

    public function hasNulls(): bool
    {
        return $this->presentPct < 1.0;
    }
}
