<?php

declare(strict_types=1);

namespace Brd6\Test\TinybirdSdk\Endpoint;

use Brd6\Test\TinybirdSdk\Mock\MockHttpClient;
use Brd6\Test\TinybirdSdk\Mock\MockResponseFactory;
use Brd6\Test\TinybirdSdk\TestCase;
use Brd6\TinybirdSdk\ClientOptions;
use Brd6\TinybirdSdk\Endpoint\PipesEndpoint;
use Brd6\TinybirdSdk\HttpClient\BatchResult;
use Brd6\TinybirdSdk\HttpClient\HttpRequestHandler;
use Brd6\TinybirdSdk\Resource\QueryResult;

class PipesEndpointTest extends TestCase
{
    public function testBatchQuerySimpleFormat(): void
    {
        $requestCount = 0;
        $capturedPipes = [];

        $mockClient = new MockHttpClient(
            function (string $method, string $url) use (&$requestCount, &$capturedPipes): MockResponseFactory {
                $requestCount++;
                // Extract pipe name from URL like /v0/pipes/my_pipe.json
                if (preg_match('/\/pipes\/([^.]+)\.json/', $url, $matches)) {
                    $capturedPipes[] = $matches[1];
                }

                return new MockResponseFactory(
                    json_encode(['data' => [], 'rows' => 0], JSON_THROW_ON_ERROR),
                );
            },
        );

        $options = (new ClientOptions())
            ->setToken('test-token')
            ->setHttpClient($mockClient);

        $handler = new HttpRequestHandler($options);
        $endpoint = new PipesEndpoint($handler);

        $results = $endpoint->batchQuery([
            'user_stats' => ['date' => '2025-01-01'],
            'event_counts' => [],
        ]);

        $this->assertCount(2, $results);
        $this->assertArrayHasKey('user_stats', $results);
        $this->assertArrayHasKey('event_counts', $results);
        $this->assertContains('user_stats', $capturedPipes);
        $this->assertContains('event_counts', $capturedPipes);
    }

    public function testBatchQueryWithHashAlias(): void
    {
        $capturedPipes = [];

        $mockClient = new MockHttpClient(
            function (string $method, string $url) use (&$capturedPipes): MockResponseFactory {
                if (preg_match('/\/pipes\/([^.]+)\.json/', $url, $matches)) {
                    $capturedPipes[] = $matches[1];
                }

                return new MockResponseFactory(
                    json_encode(['data' => [], 'rows' => 0], JSON_THROW_ON_ERROR),
                );
            },
        );

        $options = (new ClientOptions())
            ->setToken('test-token')
            ->setHttpClient($mockClient);

        $handler = new HttpRequestHandler($options);
        $endpoint = new PipesEndpoint($handler);

        // Query same pipe with different params using # alias
        $results = $endpoint->batchQuery([
            'user_stats#jan' => ['date' => '2025-01-01'],
            'user_stats#feb' => ['date' => '2025-02-01'],
        ]);

        $this->assertCount(2, $results);
        $this->assertArrayHasKey('user_stats#jan', $results);
        $this->assertArrayHasKey('user_stats#feb', $results);

        // Both should query the same pipe
        $this->assertCount(2, $capturedPipes);
        $this->assertSame('user_stats', $capturedPipes[0]);
        $this->assertSame('user_stats', $capturedPipes[1]);
    }

    public function testBatchQueryMixedFormats(): void
    {
        $capturedPipes = [];

        $mockClient = new MockHttpClient(
            function (string $method, string $url) use (&$capturedPipes): MockResponseFactory {
                if (preg_match('/\/pipes\/([^.]+)\.json/', $url, $matches)) {
                    $capturedPipes[] = $matches[1];
                }

                return new MockResponseFactory(
                    json_encode(['data' => [], 'rows' => 0], JSON_THROW_ON_ERROR),
                );
            },
        );

        $options = (new ClientOptions())
            ->setToken('test-token')
            ->setHttpClient($mockClient);

        $handler = new HttpRequestHandler($options);
        $endpoint = new PipesEndpoint($handler);

        // Mix simple and aliased formats
        $results = $endpoint->batchQuery([
            'event_counts' => [],  // Simple: key is pipe name
            'user_stats#jan' => ['date' => '2025-01-01'],  // Aliased
            'user_stats#feb' => ['date' => '2025-02-01'],  // Aliased
        ]);

        $this->assertCount(3, $results);
        $this->assertArrayHasKey('event_counts', $results);
        $this->assertArrayHasKey('user_stats#jan', $results);
        $this->assertArrayHasKey('user_stats#feb', $results);

        $this->assertContains('event_counts', $capturedPipes);
        $this->assertSame(2, array_count_values($capturedPipes)['user_stats']);
    }

    public function testBatchQueryAliasStrippedFromPipeName(): void
    {
        $capturedUrls = [];

        $mockClient = new MockHttpClient(
            function (string $method, string $url) use (&$capturedUrls): MockResponseFactory {
                $capturedUrls[] = $url;

                return new MockResponseFactory(
                    json_encode(['data' => [], 'rows' => 0], JSON_THROW_ON_ERROR),
                );
            },
        );

        $options = (new ClientOptions())
            ->setToken('test-token')
            ->setHttpClient($mockClient);

        $handler = new HttpRequestHandler($options);
        $endpoint = new PipesEndpoint($handler);

        $endpoint->batchQuery([
            'my_pipe#alias' => ['param' => 'value'],
        ]);

        // URL should contain pipe name without alias
        $this->assertCount(1, $capturedUrls);
        $this->assertStringContainsString('/pipes/my_pipe.json', $capturedUrls[0]);
        $this->assertStringNotContainsString('#', $capturedUrls[0]);
    }

    public function testBatchQueryReturnsResults(): void
    {
        $mockClient = new MockHttpClient(
            function (string $method, string $url): MockResponseFactory {
                if (str_contains($url, 'users')) {
                    return new MockResponseFactory(
                        json_encode(['data' => [['count' => 100]], 'rows' => 1], JSON_THROW_ON_ERROR),
                    );
                }

                return new MockResponseFactory(
                    json_encode(['data' => [['count' => 500]], 'rows' => 1], JSON_THROW_ON_ERROR),
                );
            },
        );

        $options = (new ClientOptions())
            ->setToken('test-token')
            ->setHttpClient($mockClient);

        $handler = new HttpRequestHandler($options);
        $endpoint = new PipesEndpoint($handler);

        $results = $endpoint->batchQuery([
            'users' => [],
            'events' => [],
        ]);

        $this->assertInstanceOf(BatchResult::class, $results['users']);
        $this->assertTrue($results['users']->isSuccess());
        $this->assertInstanceOf(QueryResult::class, $results['users']->getData());
    }

    public function testBatchQueryEmptyQueries(): void
    {
        $mockClient = new MockHttpClient(
            new MockResponseFactory(json_encode(['data' => [], 'rows' => 0], JSON_THROW_ON_ERROR)),
        );

        $options = (new ClientOptions())
            ->setToken('test-token')
            ->setHttpClient($mockClient);

        $handler = new HttpRequestHandler($options);
        $endpoint = new PipesEndpoint($handler);

        $results = $endpoint->batchQuery([]);

        $this->assertSame([], $results);
    }
}
