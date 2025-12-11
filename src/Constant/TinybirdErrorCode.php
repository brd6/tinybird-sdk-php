<?php

declare(strict_types=1);

namespace Brd6\TinybirdSdk\Constant;

final class TinybirdErrorCode
{
    public const REQUEST_TIMEOUT = 'tinybird_client_request_timeout';
    public const RESPONSE_ERROR = 'tinybird_client_response_error';

    public const UNAUTHORIZED = 'unauthorized';
    public const FORBIDDEN = 'forbidden';
    public const NOT_FOUND = 'not_found';
    public const RATE_LIMITED = 'rate_limited';
    public const VALIDATION_ERROR = 'validation_error';
    public const INTERNAL_SERVER_ERROR = 'internal_server_error';
    public const SERVICE_UNAVAILABLE = 'service_unavailable';

    public const RETRYABLE_STATUS_CODES = [429, 500, 502, 503, 504];

    public const CLIENT_ERROR_CODES = [
        self::REQUEST_TIMEOUT,
        self::RESPONSE_ERROR,
    ];

    public const API_ERROR_CODES = [
        self::UNAUTHORIZED,
        self::FORBIDDEN,
        self::NOT_FOUND,
        self::RATE_LIMITED,
        self::VALIDATION_ERROR,
        self::INTERNAL_SERVER_ERROR,
        self::SERVICE_UNAVAILABLE,
    ];
}
