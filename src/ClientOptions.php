<?php

declare(strict_types=1);

namespace Brd6\TinybirdSdk;

use Brd6\TinybirdSdk\Enum\Region;
use Psr\Http\Client\ClientInterface as HttpClientInterface;

class ClientOptions
{
    public const DEFAULT_API_VERSION = 'v0';
    public const DEFAULT_TIMEOUT = 60;
    public const DEFAULT_LOCAL_PORT = 7181;
    public const DEFAULT_RETRY_MAX_RETRIES = 3;
    public const DEFAULT_RETRY_DELAY_MS = 2000;
    public const DEFAULT_RETRY_BACKOFF_MULTIPLIER = 2;

    private const DEFAULT_REGION = Region::GCP_EUROPE_WEST3;
    private const DEFAULT_LOCAL_BASE_PATH = 'http://localhost';

    private string $token = '';
    private string $baseUrl;
    private string $apiVersion = self::DEFAULT_API_VERSION;
    private int $timeout = self::DEFAULT_TIMEOUT;
    private bool $compression = false;
    private ?HttpClientInterface $httpClient = null;
    private int $retryMaxRetries = self::DEFAULT_RETRY_MAX_RETRIES;
    private int $retryDelayMs = self::DEFAULT_RETRY_DELAY_MS;
    private int $retryBackoffMultiplier = self::DEFAULT_RETRY_BACKOFF_MULTIPLIER;

    public function __construct()
    {
        $this->baseUrl = self::DEFAULT_REGION->getBaseUrl();
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function hasToken(): bool
    {
        return $this->token !== '';
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function setBaseUrl(string $baseUrl): self
    {
        $this->baseUrl = rtrim($baseUrl, '/');

        return $this;
    }

    public function setRegion(Region $region): self
    {
        return $this->setBaseUrl($region->getBaseUrl());
    }

    public function useLocal(int $port = self::DEFAULT_LOCAL_PORT): self
    {
        return $this->setBaseUrl(self::DEFAULT_LOCAL_BASE_PATH . ':' . $port);
    }

    public function getApiVersion(): string
    {
        return $this->apiVersion;
    }

    public function setApiVersion(string $apiVersion): self
    {
        $this->apiVersion = $apiVersion;

        return $this;
    }

    public function getTimeout(): int
    {
        return $this->timeout;
    }

    public function setTimeout(int $timeout): self
    {
        $this->timeout = $timeout;

        return $this;
    }

    public function hasCompression(): bool
    {
        return $this->compression;
    }

    public function setCompression(bool $compression): self
    {
        $this->compression = $compression;

        return $this;
    }

    public function getHttpClient(): ?HttpClientInterface
    {
        return $this->httpClient;
    }

    public function setHttpClient(HttpClientInterface $httpClient): self
    {
        $this->httpClient = $httpClient;

        return $this;
    }

    public function getRetryMaxRetries(): int
    {
        return $this->retryMaxRetries;
    }

    public function setRetryMaxRetries(int $retryMaxRetries): self
    {
        $this->retryMaxRetries = $retryMaxRetries;

        return $this;
    }

    public function getRetryDelayMs(): int
    {
        return $this->retryDelayMs;
    }

    public function setRetryDelayMs(int $retryDelayMs): self
    {
        $this->retryDelayMs = $retryDelayMs;

        return $this;
    }

    public function getRetryBackoffMultiplier(): int
    {
        return $this->retryBackoffMultiplier;
    }

    public function setRetryBackoffMultiplier(int $retryBackoffMultiplier): self
    {
        $this->retryBackoffMultiplier = $retryBackoffMultiplier;

        return $this;
    }
}
