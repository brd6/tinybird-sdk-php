<?php

declare(strict_types=1);

namespace Brd6\Test\TinybirdSdk\Endpoint;

use Brd6\Test\TinybirdSdk\TestCase;
use Brd6\TinybirdSdk\ClientOptions;
use Brd6\TinybirdSdk\Endpoint\QueryEndpoint;
use Brd6\TinybirdSdk\Enum\QueryFormat;
use Brd6\TinybirdSdk\HttpClient\HttpRequestHandler;
use Brd6\TinybirdSdk\RequestParameters\QueryParams;
use Brd6\TinybirdSdk\Resource\QueryResult;
use Brd6\Test\TinybirdSdk\Mock\MockHttpClient;
use Brd6\Test\TinybirdSdk\Mock\MockResponseFactory;

class QueryEndpointTest extends TestCase
{
    /**
     * @param array<string, mixed> $responseData
     */
    private function createEndpoint(array $responseData = [], int $statusCode = 200): QueryEndpoint
    {
        $mockClient = new MockHttpClient(
            new MockResponseFactory(
                json_encode($responseData, JSON_THROW_ON_ERROR),
                ['http_code' => $statusCode],
            ),
        );

        $options = (new ClientOptions())
            ->setToken('test-token')
            ->setHttpClient($mockClient);

        $handler = new HttpRequestHandler($options);

        return new QueryEndpoint($handler);
    }

    public function testSqlReturnsQueryResult(): void
    {
        $endpoint = $this->createEndpoint([
            'data' => [['id' => 1, 'name' => 'test']],
            'meta' => [['name' => 'id', 'type' => 'Int32'], ['name' => 'name', 'type' => 'String']],
            'rows' => 1,
            'statistics' => ['elapsed' => 0.001, 'rows_read' => 1, 'bytes_read' => 100],
        ]);

        $result = $endpoint->sql('SELECT * FROM test');

        $this->assertInstanceOf(QueryResult::class, $result);
        $this->assertCount(1, $result->data);
        $this->assertSame(1, $result->rows);
        $this->assertSame(1, $result->data[0]['id']);
    }

    public function testSqlAppendsFormatJson(): void
    {
        $mockClient = new MockHttpClient(
            new MockResponseFactory(
                json_encode(['data' => [], 'rows' => 0], JSON_THROW_ON_ERROR),
                ['http_code' => 200],
            ),
        );

        $options = (new ClientOptions())
            ->setToken('test-token')
            ->setHttpClient($mockClient);

        $handler = new HttpRequestHandler($options);
        $endpoint = new QueryEndpoint($handler);

        $endpoint->sql('SELECT * FROM test');

        $lastRequest = $mockClient->getLastRequest();
        $this->assertNotNull($lastRequest);
        $query = urldecode($lastRequest->getUri()->getQuery());
        $this->assertStringContainsString('FORMAT JSON', $query);
    }

    public function testSqlPreservesExistingFormat(): void
    {
        $mockClient = new MockHttpClient(
            new MockResponseFactory(
                json_encode(['data' => [], 'rows' => 0], JSON_THROW_ON_ERROR),
                ['http_code' => 200],
            ),
        );

        $options = (new ClientOptions())
            ->setToken('test-token')
            ->setHttpClient($mockClient);

        $handler = new HttpRequestHandler($options);
        $endpoint = new QueryEndpoint($handler);

        $endpoint->sql('SELECT * FROM test FORMAT CSV');

        $lastRequest = $mockClient->getLastRequest();
        $this->assertNotNull($lastRequest);
        $query = urldecode($lastRequest->getUri()->getQuery());
        $this->assertStringContainsString('FORMAT CSV', $query);
        $this->assertStringNotContainsString('FORMAT JSON', $query);
    }

    public function testSqlWithCustomFormat(): void
    {
        $mockClient = new MockHttpClient(
            new MockResponseFactory(
                json_encode(['data' => [], 'rows' => 0], JSON_THROW_ON_ERROR),
                ['http_code' => 200],
            ),
        );

        $options = (new ClientOptions())
            ->setToken('test-token')
            ->setHttpClient($mockClient);

        $handler = new HttpRequestHandler($options);
        $endpoint = new QueryEndpoint($handler);

        $endpoint->sql('SELECT * FROM test', QueryFormat::CSV_WITH_NAMES);

        $lastRequest = $mockClient->getLastRequest();
        $this->assertNotNull($lastRequest);
        $query = urldecode($lastRequest->getUri()->getQuery());
        $this->assertStringContainsString('FORMAT CSVWithNames', $query);
    }

    public function testSqlWithQueryParams(): void
    {
        $mockClient = new MockHttpClient(
            new MockResponseFactory(
                json_encode(['data' => [], 'rows' => 0], JSON_THROW_ON_ERROR),
                ['http_code' => 200],
            ),
        );

        $options = (new ClientOptions())
            ->setToken('test-token')
            ->setHttpClient($mockClient);

        $handler = new HttpRequestHandler($options);
        $endpoint = new QueryEndpoint($handler);

        $endpoint->sql(
            'SELECT * FROM _',
            QueryFormat::JSON,
            new QueryParams(pipeline: 'my_pipe', outputFormatJsonQuote64bitIntegers: 1),
        );

        $lastRequest = $mockClient->getLastRequest();
        $this->assertNotNull($lastRequest);
        $query = $lastRequest->getUri()->getQuery();
        $this->assertStringContainsString('pipeline=my_pipe', $query);
        $this->assertStringContainsString('output_format_json_quote_64bit_integers=1', $query);
    }

    public function testSqlPipeline(): void
    {
        $mockClient = new MockHttpClient(
            new MockResponseFactory(
                json_encode(['data' => [], 'rows' => 0], JSON_THROW_ON_ERROR),
                ['http_code' => 200],
            ),
        );

        $options = (new ClientOptions())
            ->setToken('test-token')
            ->setHttpClient($mockClient);

        $handler = new HttpRequestHandler($options);
        $endpoint = new QueryEndpoint($handler);

        $endpoint->sqlPipeline('SELECT count() FROM _', 'my_events_pipe');

        $lastRequest = $mockClient->getLastRequest();
        $this->assertNotNull($lastRequest);
        $query = $lastRequest->getUri()->getQuery();
        $this->assertStringContainsString('pipeline=my_events_pipe', $query);
    }

    public function testSqlPostSendsJsonBody(): void
    {
        $mockClient = new MockHttpClient(
            new MockResponseFactory(
                json_encode(['data' => [], 'rows' => 0], JSON_THROW_ON_ERROR),
                ['http_code' => 200],
            ),
        );

        $options = (new ClientOptions())
            ->setToken('test-token')
            ->setHttpClient($mockClient);

        $handler = new HttpRequestHandler($options);
        $endpoint = new QueryEndpoint($handler);

        $endpoint->sqlPost(
            '% SELECT * FROM events WHERE status = {{String(status)}}',
            ['status' => 'active'],
        );

        $lastRequest = $mockClient->getLastRequest();
        $this->assertNotNull($lastRequest);
        $this->assertSame('POST', $lastRequest->getMethod());
        $this->assertStringContainsString('application/json', $lastRequest->getHeaderLine('Content-Type'));

        $body = json_decode((string) $lastRequest->getBody(), true);
        $this->assertArrayHasKey('q', $body);
        $this->assertArrayHasKey('status', $body);
        $this->assertSame('active', $body['status']);
    }

    public function testQueryResultHelpers(): void
    {
        $endpoint = $this->createEndpoint([
            'data' => [['id' => 1], ['id' => 2]],
            'meta' => [['name' => 'id', 'type' => 'Int32']],
            'rows' => 2,
            'rows_before_limit_at_least' => 100,
            'statistics' => ['elapsed' => 0.005, 'rows_read' => 100, 'bytes_read' => 1024],
        ]);

        $result = $endpoint->sql('SELECT id FROM test');

        $this->assertSame(0.005, $result->getElapsedTime());
        $this->assertSame(100, $result->getRowsRead());
        $this->assertSame(1024, $result->getBytesRead());
        $this->assertSame(['id'], $result->getColumnNames());
        $this->assertFalse($result->isEmpty());
        $this->assertSame(100, $result->rowsBeforeLimitAtLeast);
    }

    public function testQueryResultIsEmpty(): void
    {
        $endpoint = $this->createEndpoint([
            'data' => [],
            'meta' => [],
            'rows' => 0,
        ]);

        $result = $endpoint->sql('SELECT * FROM test WHERE 1=0');

        $this->assertTrue($result->isEmpty());
        $this->assertSame([], $result->data);
    }

    public function testQueryResultPreservesRawData(): void
    {
        $rawData = [
            'data' => [],
            'meta' => [],
            'rows' => 0,
            'custom_field' => 'preserved',
        ];

        $endpoint = $this->createEndpoint($rawData);
        $result = $endpoint->sql('SELECT 1');

        $this->assertSame($rawData, $result->getRawData());
    }
}
