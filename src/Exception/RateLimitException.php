<?php

declare(strict_types=1);

namespace Brd6\TinybirdSdk\Exception;

use Brd6\TinybirdSdk\Constant\TinybirdErrorCode;

class RateLimitException extends ApiException
{
    private ?int $retryAfter = null;
    private ?int $rateLimitLimit = null;
    private ?int $rateLimitRemaining = null;
    private ?int $rateLimitReset = null;

    /**
     * @param array<string, list<string>> $headers
     * @param array<string, mixed> $response
     */
    public function __construct(
        int $statusCode,
        array $headers = [],
        array $response = [],
        string $message = '',
    ) {
        parent::__construct($statusCode, $headers, $response, $message !== '' ? $message : 'Rate limit exceeded');

        $this->retryAfter = $this->getHeaderInt($headers, 'retry-after');
        $this->rateLimitLimit = $this->getHeaderInt($headers, 'x-ratelimit-limit');
        $this->rateLimitRemaining = $this->getHeaderInt($headers, 'x-ratelimit-remaining');
        $this->rateLimitReset = $this->getHeaderInt($headers, 'x-ratelimit-reset');
    }

    public function getErrorCode(): string
    {
        return TinybirdErrorCode::RATE_LIMITED;
    }

    public function getRetryAfter(): ?int
    {
        return $this->retryAfter;
    }

    public function getRateLimitLimit(): ?int
    {
        return $this->rateLimitLimit;
    }

    public function getRateLimitRemaining(): ?int
    {
        return $this->rateLimitRemaining;
    }

    public function getRateLimitReset(): ?int
    {
        return $this->rateLimitReset;
    }
}
