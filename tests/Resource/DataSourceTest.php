<?php

declare(strict_types=1);

namespace Brd6\Test\TinybirdSdk\Resource;

use Brd6\Test\TinybirdSdk\TestCase;
use Brd6\TinybirdSdk\Resource\Column;
use Brd6\TinybirdSdk\Resource\DataSource;
use Brd6\TinybirdSdk\Resource\Engine;
use Brd6\TinybirdSdk\Resource\Statistics;

class DataSourceTest extends TestCase
{
    /**
     * @return array<string, mixed>
     */
    private function createDataSourceData(): array
    {
        return [
            'id' => 't_123abc',
            'name' => 'events',
            'cluster' => 'tinybird',
            'description' => 'Event tracking',
            'type' => 'csv',
            'replicated' => true,
            'version' => 1,
            'project' => 'my_project',
            'quarantine_rows' => 5,
            'created_at' => '2024-01-15 10:30:00',
            'updated_at' => '2024-01-16 12:00:00',
            'tags' => ['env' => 'production'],
            'shared_with' => ['workspace_1', 'workspace_2'],
            'engine' => [
                'engine' => 'MergeTree',
                'engine_sorting_key' => 'timestamp',
                'engine_partition_key' => 'toYYYYMM(timestamp)',
            ],
            'columns' => [
                ['name' => 'id', 'type' => 'Int64', 'nullable' => false],
                ['name' => 'timestamp', 'type' => 'DateTime', 'nullable' => false],
                ['name' => 'event', 'type' => 'String', 'nullable' => true],
            ],
            'statistics' => [
                'bytes' => 1024000,
                'row_count' => 50000,
            ],
        ];
    }

    public function testFromArrayParsesBasicFields(): void
    {
        $ds = DataSource::fromArray($this->createDataSourceData());

        $this->assertSame('t_123abc', $ds->id);
        $this->assertSame('events', $ds->name);
        $this->assertSame('tinybird', $ds->cluster);
        $this->assertSame('Event tracking', $ds->description);
        $this->assertSame('csv', $ds->type);
        $this->assertTrue($ds->replicated);
        $this->assertSame(1, $ds->version);
        $this->assertSame('my_project', $ds->project);
        $this->assertSame(5, $ds->quarantineRows);
    }

    public function testFromArrayParsesDateTimes(): void
    {
        $ds = DataSource::fromArray($this->createDataSourceData());

        $this->assertNotNull($ds->createdAt);
        $this->assertSame('2024-01-15', $ds->createdAt->format('Y-m-d'));
        $this->assertNotNull($ds->updatedAt);
        $this->assertSame('2024-01-16', $ds->updatedAt->format('Y-m-d'));
    }

    public function testFromArrayParsesEngine(): void
    {
        $ds = DataSource::fromArray($this->createDataSourceData());

        $this->assertInstanceOf(Engine::class, $ds->engine);
        $this->assertSame('MergeTree', $ds->engine->engine);
        $this->assertSame('timestamp', $ds->engine->engineSortingKey);
    }

    public function testFromArrayParsesColumns(): void
    {
        $ds = DataSource::fromArray($this->createDataSourceData());

        $this->assertCount(3, $ds->columns);
        $this->assertInstanceOf(Column::class, $ds->columns[0]);
        $this->assertSame('id', $ds->columns[0]->name);
        $this->assertSame('Int64', $ds->columns[0]->type);
    }

    public function testFromArrayParsesStatistics(): void
    {
        $ds = DataSource::fromArray($this->createDataSourceData());

        $this->assertInstanceOf(Statistics::class, $ds->statistics);
        $this->assertSame(1024000, $ds->statistics->bytes);
        $this->assertSame(50000, $ds->statistics->rowCount);
    }

    public function testGetColumnByName(): void
    {
        $ds = DataSource::fromArray($this->createDataSourceData());

        $col = $ds->getColumn('timestamp');
        $this->assertNotNull($col);
        $this->assertSame('DateTime', $col->type);

        $this->assertNull($ds->getColumn('nonexistent'));
    }

    public function testHelperMethods(): void
    {
        $ds = DataSource::fromArray($this->createDataSourceData());

        $this->assertTrue($ds->hasQuarantinedRows());
        $this->assertTrue($ds->isShared());
        $this->assertSame(50000, $ds->getRowCount());
        $this->assertSame(1024000, $ds->getBytes());
    }

    public function testFromArrayWithMinimalData(): void
    {
        $ds = DataSource::fromArray([
            'id' => 't_minimal',
            'name' => 'minimal',
        ]);

        $this->assertSame('t_minimal', $ds->id);
        $this->assertSame('minimal', $ds->name);
        $this->assertNull($ds->engine);
        $this->assertNull($ds->statistics);
        $this->assertCount(0, $ds->columns);
        $this->assertFalse($ds->hasQuarantinedRows());
        $this->assertFalse($ds->isShared());
    }

    public function testGetRawDataPreservesOriginal(): void
    {
        $data = $this->createDataSourceData();
        $ds = DataSource::fromArray($data);

        $this->assertSame($data, $ds->getRawData());
    }
}
