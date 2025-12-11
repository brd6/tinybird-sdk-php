<?php

declare(strict_types=1);

namespace Brd6\Test\TinybirdSdk\Mock;

use Brd6\TinybirdSdk\Constant\HttpStatusCode;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;

class MockResponseFactory
{
    /**
     * @param array{http_code?: int, headers?: array<string, string>} $options
     */
    public function __construct(
        private readonly string $body,
        private readonly array $options = [],
    ) {
    }

    public function createResponse(): ResponseInterface
    {
        return new Response(
            $this->options['http_code'] ?? HttpStatusCode::OK,
            $this->options['headers'] ?? [],
            $this->body,
        );
    }
}
