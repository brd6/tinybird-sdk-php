<?php

declare(strict_types=1);

namespace Brd6\Test\TinybirdSdk\Mock;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

use function is_callable;
use function parse_str;

class MockHttpClient implements ClientInterface
{
    /** @var MockResponseFactory|callable(string $method, string $url, array $options): MockResponseFactory */
    private $responseFactory;

    private ?RequestInterface $lastRequest = null;

    public function __construct(MockResponseFactory|callable $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $this->lastRequest = $request;

        if (is_callable($this->responseFactory)) {
            return ($this->responseFactory)(
                $request->getMethod(),
                (string) $request->getUri(),
                $this->extractRequestOptions($request),
            )->createResponse();
        }

        return $this->responseFactory->createResponse();
    }

    public function getLastRequest(): ?RequestInterface
    {
        return $this->lastRequest;
    }

    /**
     * @return array{body: string, query: array<string, string>}
     */
    private function extractRequestOptions(RequestInterface $request): array
    {
        $query = [];
        parse_str($request->getUri()->getQuery(), $query);

        return [
            'body' => (string) $request->getBody(),
            'query' => $query,
        ];
    }
}
