<?php

declare(strict_types=1);

namespace Brd6\TinybirdSdk\Endpoint;

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
}
