<?php

declare(strict_types=1);

namespace Brd6\TinybirdSdk\Resource;

class AnalyzeResult extends AbstractResource
{
    /** @var array<AnalyzedColumn> */
    public array $columns = [];

    public string $schema = '';

    /** @var array<array<string, mixed>> */
    public array $previewData = [];

    public int $previewRows = 0;

    protected function initialize(): void
    {
        $data = $this->getRawData();
        $analysis = $data['analysis'] ?? [];
        $preview = $data['preview'] ?? [];

        $this->schema = (string) ($analysis['schema'] ?? '');

        $this->columns = array_map(
            static fn (array $col) => AnalyzedColumn::fromArray($col),
            (array) ($analysis['columns'] ?? []),
        );

        $this->previewData = (array) ($preview['data'] ?? []);
        $this->previewRows = (int) ($preview['rows'] ?? 0);
    }

    public function getSchema(): string
    {
        return $this->schema;
    }

    /**
     * @return array<string>
     */
    public function getColumnNames(): array
    {
        return array_map(
            static fn (AnalyzedColumn $col) => $col->name,
            $this->columns,
        );
    }
}
