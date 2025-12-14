<?php

declare(strict_types=1);

namespace Brd6\TinybirdSdk\Endpoint;

use Brd6\TinybirdSdk\HttpClient\BatchRequestItem;
use Brd6\TinybirdSdk\HttpClient\BatchResult;
use Brd6\TinybirdSdk\HttpClient\HttpRequestHandler;

abstract class AbstractEndpoint
{
    public function __construct(
        protected readonly HttpRequestHandler $handler,
    ) {
    }

    /**
     * @param array<string, mixed> $query
     * @return array<string, mixed>
     */
    protected function get(string $path, array $query = []): array
    {
        return $this->handler->request('GET', $path, $query);
    }

    /**
     * @param array<string, mixed>|string|null $body
     * @param array<string, mixed> $query
     * @param array<string, string> $headers
     * @return array<string, mixed>
     */
    protected function post(string $path, array|string|null $body = null, array $query = [], array $headers = []): array
    {
        return $this->handler->request('POST', $path, $query, $body, $headers);
    }

    /**
     * @param array<string, mixed>|string|null $body
     * @param array<string, mixed> $query
     * @param array<string, string> $headers
     * @return array<string, mixed>
     */
    protected function put(string $path, array|string|null $body = null, array $query = [], array $headers = []): array
    {
        return $this->handler->request('PUT', $path, $query, $body, $headers);
    }

    /**
     * @param array<string, mixed>|string|null $body
     * @param array<string, mixed> $query
     * @param array<string, string> $headers
     * @return array<string, mixed>
     */
    protected function patch(
        string $path,
        array|string|null $body = null,
        array $query = [],
        array $headers = [],
    ): array {
        return $this->handler->request('PATCH', $path, $query, $body, $headers);
    }

    /**
     * @param array<string, mixed> $query
     * @return array<string, mixed>
     */
    protected function delete(string $path, array $query = []): array
    {
        return $this->handler->request('DELETE', $path, $query);
    }

    /**
     * @param array<int|string, BatchRequestItem> $requests
     * @return array<int|string, BatchResult<array<string, mixed>>>
     */
    protected function batchRequest(array $requests): array
    {
        return $this->handler->batchRequest($requests);
    }

    /**
     * @template T
     * @param array<int|string, BatchResult<array<string, mixed>>> $responses
     * @param callable(array<string, mixed>): T $transformer
     * @return array<string, BatchResult<T>>
     */
    protected function transformBatchResponses(array $responses, callable $transformer): array
    {
        /** @var array<string, BatchResult<T>> $results */
        $results = [];

        foreach ($responses as $key => $response) {
            $resultKey = (string) $key;
            if ($response->isSuccess()) {
                $results[$resultKey] = BatchResult::success($transformer($response->getData()));
            } else {
                $exception = $response->getException();
                if ($exception !== null) {
                    $results[$resultKey] = BatchResult::failure($exception);
                }
            }
        }

        return $results;
    }
}
