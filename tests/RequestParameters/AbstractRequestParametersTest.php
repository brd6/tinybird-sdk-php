<?php

declare(strict_types=1);

namespace Brd6\Test\TinybirdSdk\RequestParameters;

use Brd6\Test\TinybirdSdk\TestCase;
use Brd6\TinybirdSdk\Enum\JobKind;
use Brd6\TinybirdSdk\Enum\JobStatus;
use Brd6\TinybirdSdk\RequestParameters\JobsListParams;
use DateTimeImmutable;

class AbstractRequestParametersTest extends TestCase
{
    public function testConvertsKeysToSnakeCase(): void
    {
        $params = new JobsListParams(pipeName: 'test_pipe');
        $result = $params->jsonSerialize();

        $this->assertArrayHasKey('pipe_name', $result);
        $this->assertArrayNotHasKey('pipeName', $result);
    }

    public function testConvertsBoolToString(): void
    {
        $params = new \Brd6\TinybirdSdk\RequestParameters\QueryParams(
            outputFormatJsonQuote64bitIntegers: 1,
        );
        $result = $params->jsonSerialize();

        $this->assertArrayHasKey('output_format_json_quote_64bit_integers', $result);
    }

    public function testConvertsDateTimeToIso8601(): void
    {
        $date = new DateTimeImmutable('2024-01-15T10:30:00+00:00');
        $params = new JobsListParams(createdAfter: $date);
        $result = $params->jsonSerialize();

        $this->assertSame('2024-01-15T10:30:00+00:00', $result['created_after']);
    }

    public function testConvertsEnumToValue(): void
    {
        $params = new JobsListParams(status: JobStatus::DONE);
        $result = $params->jsonSerialize();

        $this->assertSame('done', $result['status']);
    }

    public function testConvertsKindEnumToValue(): void
    {
        $params = new JobsListParams(kind: JobKind::IMPORT);
        $result = $params->jsonSerialize();

        $this->assertSame('import', $result['kind']);
    }

    public function testIgnoresNullValues(): void
    {
        $params = new JobsListParams(pipeName: 'test');
        $result = $params->jsonSerialize();

        $this->assertArrayHasKey('pipe_name', $result);
        $this->assertArrayNotHasKey('status', $result);
        $this->assertArrayNotHasKey('kind', $result);
    }

    public function testToArrayReturnsSnakeCaseKeys(): void
    {
        $params = new JobsListParams(pipeName: 'test', pipeId: 'id_123');
        $result = $params->toArray();

        $this->assertArrayHasKey('pipe_name', $result);
        $this->assertArrayHasKey('pipe_id', $result);
    }
}
