<?php

declare(strict_types=1);

namespace Brd6\Test\TinybirdSdk\HttpClient;

use Brd6\Test\TinybirdSdk\TestCase;
use Brd6\TinybirdSdk\ClientOptions;
use Brd6\TinybirdSdk\Exception\ApiException;
use Brd6\TinybirdSdk\Exception\AuthenticationException;
use Brd6\TinybirdSdk\Exception\RateLimitException;
use Brd6\TinybirdSdk\Exception\RequestTimeoutException;
use Brd6\TinybirdSdk\HttpClient\HttpClientFactory;
use Brd6\TinybirdSdk\HttpClient\HttpRequestHandler;
use Http\Client\Common\HttpMethodsClientInterface;
use Http\Client\Exception\NetworkException;
use Nyholm\Psr7\Request;
use Nyholm\Psr7\Response;

class HttpRequestHandlerTest extends TestCase
{
    private function createHandler(HttpMethodsClientInterface $httpClient): HttpRequestHandler
    {
        $options = (new ClientOptions())->setRetryDelayMs(0);
        $factory = $this->mockery(HttpClientFactory::class);
        $factory->shouldReceive('create')->andReturn($httpClient);

        return new HttpRequestHandler($options, $factory);
    }

    public function testSuccessfulRequest(): void
    {
        $httpClient = $this->mockery(HttpMethodsClientInterface::class);
        $httpClient->shouldReceive('send')
            ->once()
            ->andReturn(new Response(200, [], '{"data": "test"}'));

        $handler = $this->createHandler($httpClient);
        $result = $handler->request('GET', '/v0/test');

        $this->assertSame(['data' => 'test'], $result);
    }

    public function testEmptyResponse(): void
    {
        $httpClient = $this->mockery(HttpMethodsClientInterface::class);
        $httpClient->shouldReceive('send')
            ->once()
            ->andReturn(new Response(204, [], ''));

        $handler = $this->createHandler($httpClient);
        $result = $handler->request('DELETE', '/v0/test');

        $this->assertSame([], $result);
    }

    public function testRequestWithQueryParams(): void
    {
        $httpClient = $this->mockery(HttpMethodsClientInterface::class);
        $httpClient->shouldReceive('send')
            ->once()
            ->withArgs(function (string $method, string $path) {
                return $method === 'GET' && str_contains($path, 'foo=bar');
            })
            ->andReturn(new Response(200, [], '{}'));

        $handler = $this->createHandler($httpClient);
        $handler->request('GET', '/v0/test', ['foo' => 'bar']);
    }

    public function testRequestWithExistingQueryParams(): void
    {
        $httpClient = $this->mockery(HttpMethodsClientInterface::class);
        $httpClient->shouldReceive('send')
            ->once()
            ->withArgs(function (string $method, string $path) {
                return str_contains($path, 'existing=1') && str_contains($path, 'new=2');
            })
            ->andReturn(new Response(200, [], '{}'));

        $handler = $this->createHandler($httpClient);
        $handler->request('GET', '/v0/test?existing=1', ['new' => '2']);
    }

    public function testRequestWithJsonBody(): void
    {
        $httpClient = $this->mockery(HttpMethodsClientInterface::class);
        $httpClient->shouldReceive('send')
            ->once()
            ->withArgs(function (string $method, string $path, array $headers, ?string $body) {
                return $body === '{"key":"value"}' && ($headers['Content-Type'] ?? '') === 'application/json';
            })
            ->andReturn(new Response(200, [], '{}'));

        $handler = $this->createHandler($httpClient);
        $handler->request('POST', '/v0/test', [], ['key' => 'value']);
    }

    public function testRequestWithStringBody(): void
    {
        $httpClient = $this->mockery(HttpMethodsClientInterface::class);
        $httpClient->shouldReceive('send')
            ->once()
            ->withArgs(function (string $method, string $path, array $headers, ?string $body) {
                return $body === 'raw-content';
            })
            ->andReturn(new Response(200, [], '{}'));

        $handler = $this->createHandler($httpClient);
        $handler->request('POST', '/v0/test', [], 'raw-content');
    }

    public function testUnauthorizedThrowsAuthenticationException(): void
    {
        $httpClient = $this->mockery(HttpMethodsClientInterface::class);
        $httpClient->shouldReceive('send')
            ->once()
            ->andReturn(new Response(401, [], '{"error": "Unauthorized"}'));

        $handler = $this->createHandler($httpClient);

        $this->expectException(AuthenticationException::class);
        $handler->request('GET', '/v0/test');
    }

    public function testForbiddenThrowsAuthenticationException(): void
    {
        $httpClient = $this->mockery(HttpMethodsClientInterface::class);
        $httpClient->shouldReceive('send')
            ->once()
            ->andReturn(new Response(403, [], '{"error": "Forbidden"}'));

        $handler = $this->createHandler($httpClient);

        $this->expectException(AuthenticationException::class);
        $handler->request('GET', '/v0/test');
    }

    public function testRateLimitThrowsRateLimitException(): void
    {
        $httpClient = $this->mockery(HttpMethodsClientInterface::class);
        $httpClient->shouldReceive('send')
            ->times(3)
            ->andReturn(new Response(429, [], '{"error": "Rate limited"}'));

        $handler = $this->createHandler($httpClient);

        $this->expectException(RateLimitException::class);
        $handler->request('GET', '/v0/test');
    }

    public function testServerErrorThrowsApiException(): void
    {
        $httpClient = $this->mockery(HttpMethodsClientInterface::class);
        $httpClient->shouldReceive('send')
            ->times(3)
            ->andReturn(new Response(500, [], '{"error": "Server error"}'));

        $handler = $this->createHandler($httpClient);

        $this->expectException(ApiException::class);
        $handler->request('GET', '/v0/test');
    }

    public function testBadRequestThrowsApiException(): void
    {
        $httpClient = $this->mockery(HttpMethodsClientInterface::class);
        $httpClient->shouldReceive('send')
            ->once()
            ->andReturn(new Response(400, [], '{"error": "Bad request"}'));

        $handler = $this->createHandler($httpClient);

        $this->expectException(ApiException::class);
        $handler->request('GET', '/v0/test');
    }

    public function testRetryOnServerError(): void
    {
        $httpClient = $this->mockery(HttpMethodsClientInterface::class);
        $httpClient->shouldReceive('send')
            ->once()
            ->andReturn(new Response(500, [], '{}'));
        $httpClient->shouldReceive('send')
            ->once()
            ->andReturn(new Response(200, [], '{"success": true}'));

        $handler = $this->createHandler($httpClient);
        $result = $handler->request('GET', '/v0/test');

        $this->assertSame(['success' => true], $result);
    }

    public function testNetworkExceptionThrowsTimeoutException(): void
    {
        $httpClient = $this->mockery(HttpMethodsClientInterface::class);
        $httpClient->shouldReceive('send')
            ->times(3)
            ->andThrow(new NetworkException('Connection failed', new Request('GET', '/test')));

        $handler = $this->createHandler($httpClient);

        $this->expectException(RequestTimeoutException::class);
        $handler->request('GET', '/v0/test');
    }

    public function testRetryOnNetworkException(): void
    {
        $httpClient = $this->mockery(HttpMethodsClientInterface::class);
        $httpClient->shouldReceive('send')
            ->once()
            ->andThrow(new NetworkException('Connection failed', new Request('GET', '/test')));
        $httpClient->shouldReceive('send')
            ->once()
            ->andReturn(new Response(200, [], '{"recovered": true}'));

        $handler = $this->createHandler($httpClient);
        $result = $handler->request('GET', '/v0/test');

        $this->assertSame(['recovered' => true], $result);
    }

    public function testExceptionContainsResponseData(): void
    {
        $httpClient = $this->mockery(HttpMethodsClientInterface::class);
        $httpClient->shouldReceive('send')
            ->once()
            ->andReturn(new Response(400, ['X-Custom' => 'header'], '{"error": "Bad request", "code": 123}'));

        $handler = $this->createHandler($httpClient);

        try {
            $handler->request('GET', '/v0/test');
            $this->fail('Expected ApiException');
        } catch (ApiException $e) {
            $this->assertSame(400, $e->getCode());
            $this->assertSame('Bad request', $e->getMessage());
            $this->assertSame(['error' => 'Bad request', 'code' => 123], $e->getResponse());
            $this->assertArrayHasKey('X-Custom', $e->getHeaders());
        }
    }
}
