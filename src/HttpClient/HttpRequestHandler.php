<?php

declare(strict_types=1);

namespace Brd6\TinybirdSdk\HttpClient;

use Brd6\TinybirdSdk\ClientOptions;
use Brd6\TinybirdSdk\Constant\HttpStatusCode;
use Brd6\TinybirdSdk\Exception\ApiException;
use Brd6\TinybirdSdk\Exception\AuthenticationException;
use Brd6\TinybirdSdk\Exception\RateLimitException;
use Brd6\TinybirdSdk\Exception\RequestTimeoutException;
use Brd6\TinybirdSdk\Util\UrlHelper;
use Http\Client\Common\HttpMethodsClientInterface;
use Http\Client\Exception as HttpClientException;
use Http\Client\Exception\HttpException;
use Http\Client\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

use function count;
use function in_array;
use function json_decode;
use function json_encode;
use function json_last_error;
use function ltrim;
use function sprintf;
use function str_contains;
use function usleep;

use const JSON_ERROR_NONE;
use const JSON_THROW_ON_ERROR;

class HttpRequestHandler
{
    private const MS_TO_MICROSECONDS = 1000;
    private const RETRY_AFTER_HEADER = 'retry-after';

    private HttpMethodsClientInterface $httpClient;
    private string $apiVersion;
    private string $token;
    private int $retryMaxRetries;
    private int $retryDelayMs;
    private int $retryBackoffMultiplier;

    public function __construct(ClientOptions $options, ?HttpClientFactory $httpClientFactory = null)
    {
        $httpClientFactory ??= new HttpClientFactory();
        $this->httpClient = $httpClientFactory->create($options);
        $this->apiVersion = $options->getApiVersion();
        $this->token = $options->getToken();
        $this->retryMaxRetries = $options->getRetryMaxRetries();
        $this->retryDelayMs = $options->getRetryDelayMs();
        $this->retryBackoffMultiplier = $options->getRetryBackoffMultiplier();
    }

    /**
     * @param array<string, mixed> $query
     * @param array<string, mixed>|string|null $body
     * @param array<string, string> $headers
     * @return array<string, mixed>
     */
    public function request(
        string $method,
        string $path,
        array $query = [],
        array|string|null $body = null,
        array $headers = [],
    ): array {
        $path = $this->buildPath($path, $query);
        $bodyContent = $this->prepareBody($body, $headers);

        return $this->executeWithRetry(
            fn () => $this->httpClient->send($method, $path, $headers, $bodyContent),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function executeWithRetry(callable $httpCall): array
    {
        $delay = $this->retryDelayMs;

        for ($attempt = 0; $attempt < $this->retryMaxRetries; ++$attempt) {
            try {
                $response = $this->sendRequest($httpCall);

                if ($this->isSuccessResponse($response)) {
                    return $this->parseResponse($response);
                }

                if ($this->shouldRetry($response->getStatusCode(), $attempt)) {
                    $delay = $this->waitAndGetNextDelay($delay, $response);
                    continue;
                }

                throw $this->createExceptionFromResponse($response);
            } catch (HttpClientException $e) {
                if ($this->canRetry($attempt)) {
                    $delay = $this->waitAndGetNextDelay($delay);
                    continue;
                }

                throw new RequestTimeoutException($e->getMessage());
            }
        }

        throw new ApiException(0, [], [], 'Max retries exceeded');
    }

    private function sendRequest(callable $httpCall): ResponseInterface
    {
        try {
            return $httpCall();
        } catch (RequestException $e) {
            if ($e instanceof HttpException) {
                throw $this->createExceptionFromResponse($e->getResponse());
            }
            throw new RequestTimeoutException();
        }
    }

    private function isSuccessResponse(ResponseInterface $response): bool
    {
        return $response->getStatusCode() < HttpStatusCode::MULTIPLE_CHOICES;
    }

    private function shouldRetry(int $statusCode, int $attempt): bool
    {
        return $this->canRetry($attempt)
            && in_array($statusCode, HttpStatusCode::RETRYABLE_STATUS_CODES, true);
    }

    private function canRetry(int $attempt): bool
    {
        return $attempt < $this->retryMaxRetries - 1;
    }

    private function waitAndGetNextDelay(int $currentDelay, ?ResponseInterface $response = null): int
    {
        $nextDelay = $response !== null
            ? $this->calculateDelayFromResponse($currentDelay, $response)
            : $currentDelay * $this->retryBackoffMultiplier;

        usleep($nextDelay * self::MS_TO_MICROSECONDS);

        return $nextDelay;
    }

    private function calculateDelayFromResponse(int $currentDelay, ResponseInterface $response): int
    {
        $retryAfter = $this->getRetryAfterHeader($response);

        return $retryAfter > 0
            ? $retryAfter * self::MS_TO_MICROSECONDS
            : $currentDelay * $this->retryBackoffMultiplier;
    }

    private function getRetryAfterHeader(ResponseInterface $response): int
    {
        foreach ($response->getHeaders() as $name => $values) {
            if (strtolower((string) $name) === self::RETRY_AFTER_HEADER) {
                return (int) ($values[0] ?? 0);
            }
        }

        return 0;
    }

    /**
     * @param array<string, mixed> $query
     */
    private function buildPath(string $path, array $query): string
    {
        $path = '/' . $this->apiVersion . '/' . ltrim($path, '/');

        if (count($query) === 0) {
            return $path;
        }

        $separator = str_contains($path, '?') ? '&' : '?';

        return $path . $separator . UrlHelper::buildQuery($query);
    }

    /**
     * @param array<string, mixed>|string|null $body
     * @param array<string, string> $headers
     */
    private function prepareBody(array|string|null $body, array &$headers): ?string
    {
        if ($body === null || $body === '' || (is_array($body) && count($body) === 0)) {
            return null;
        }

        if (is_string($body)) {
            return $body;
        }

        $headers['Content-Type'] = 'application/json';

        return json_encode($body, JSON_THROW_ON_ERROR);
    }

    /**
     * @return array<string, mixed>
     */
    private function parseResponse(ResponseInterface $response): array
    {
        $contents = $response->getBody()->getContents();

        if ($contents === '') {
            return [];
        }

        $data = json_decode($contents, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException(
                sprintf('Unable to parse response body into JSON: %s', json_last_error()),
            );
        }

        return $data;
    }

    private function createExceptionFromResponse(ResponseInterface $response): ApiException
    {
        $statusCode = $response->getStatusCode();
        $headers = $response->getHeaders();
        $rawData = $this->parseResponseSafely($response);
        $message = $rawData['error'] ?? '';

        if (in_array($statusCode, HttpStatusCode::AUTHENTICATION_ERROR_STATUS_CODES, true)) {
            return new AuthenticationException($statusCode, $headers, $rawData, $message, $this->token);
        }

        if ($statusCode === HttpStatusCode::TOO_MANY_REQUESTS) {
            return new RateLimitException($statusCode, $headers, $rawData, $message);
        }

        return new ApiException($statusCode, $headers, $rawData, $message);
    }

    /**
     * @return array<string, mixed>
     */
    private function parseResponseSafely(ResponseInterface $response): array
    {
        try {
            return $this->parseResponse($response);
        } catch (RuntimeException) {
            return [];
        }
    }
}
