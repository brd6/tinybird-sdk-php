<?php

declare(strict_types=1);

namespace Brd6\Test\TinybirdSdk\Resource;

use Brd6\Test\TinybirdSdk\TestCase;
use Brd6\TinybirdSdk\Resource\QueryResult;

class AbstractJsonSerializableTest extends TestCase
{
    public function testToArrayPreservesEmptyArrays(): void
    {
        $result = QueryResult::fromArray([
            'data' => [],
            'meta' => [],
            'rows' => 0,
        ]);

        $array = $result->toArray();

        $this->assertArrayHasKey('data', $array);
        $this->assertSame([], $array['data']);
        $this->assertArrayHasKey('meta', $array);
        $this->assertSame([], $array['meta']);
    }

    public function testToArrayPreservesZeroValues(): void
    {
        $result = QueryResult::fromArray([
            'data' => [],
            'rows' => 0,
            'rows_before_limit_at_least' => 0,
        ]);

        $array = $result->toArray();

        $this->assertArrayHasKey('rows', $array);
        $this->assertSame(0, $array['rows']);
        $this->assertArrayHasKey('rowsBeforeLimitAtLeast', $array);
        $this->assertSame(0, $array['rowsBeforeLimitAtLeast']);
    }

    public function testToArrayWithIgnoreEmptyTrue(): void
    {
        $result = QueryResult::fromArray([
            'data' => [],
            'rows' => 0,
        ]);

        $array = $result->toArray(ignoreEmptyValue: true);

        $this->assertArrayNotHasKey('data', $array);
        $this->assertArrayHasKey('rows', $array);
    }

    public function testGetRawDataReturnsOriginal(): void
    {
        $original = [
            'data' => [['id' => 1]],
            'meta' => [['name' => 'id', 'type' => 'Int32']],
            'rows' => 1,
            'statistics' => ['elapsed' => 0.001],
            'extra_field' => 'preserved',
        ];

        $result = QueryResult::fromArray($original);

        $this->assertSame($original, $result->getRawData());
    }
}
