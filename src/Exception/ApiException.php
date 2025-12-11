<?php

declare(strict_types=1);

namespace Brd6\TinybirdSdk\Exception;

use Brd6\TinybirdSdk\Constant\TinybirdErrorCode;

class ApiException extends TinybirdException
{
    public const MESSAGE = 'Request to Tinybird API failed with status: %s';

    /** @var array<string, mixed> */
    private array $response;

    /** @var array<string, list<string>> */
    private array $headers;

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
        $message = $message !== '' ? $message : sprintf(self::MESSAGE, $statusCode);

        parent::__construct($message, $statusCode);

        $this->response = $response;
        $this->headers = $headers;
    }

    public function getErrorCode(): string
    {
        return TinybirdErrorCode::RESPONSE_ERROR;
    }

    /**
     * @return array<string, mixed>
     */
    public function getResponse(): array
    {
        return $this->response;
    }

    /**
     * @return array<string, list<string>>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @param array<string, list<string>> $headers
     */
    protected function getHeaderValue(array $headers, string $name): ?string
    {
        foreach ($headers as $key => $values) {
            if (strtolower((string) $key) === strtolower($name) && isset($values[0])) {
                return $values[0];
            }
        }

        return null;
    }

    /**
     * @param array<string, list<string>> $headers
     */
    protected function getHeaderInt(array $headers, string $name): ?int
    {
        $value = $this->getHeaderValue($headers, $name);

        return $value !== null ? (int) $value : null;
    }
}
