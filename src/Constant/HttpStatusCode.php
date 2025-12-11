<?php

declare(strict_types=1);

namespace Brd6\TinybirdSdk\Constant;

final class HttpStatusCode
{
    // Success
    public const OK = 200;

    // Redirection
    public const MULTIPLE_CHOICES = 300;

    // Client Errors
    public const BAD_REQUEST = 400;
    public const UNAUTHORIZED = 401;
    public const FORBIDDEN = 403;
    public const NOT_FOUND = 404;
    public const TOO_MANY_REQUESTS = 429;

    // Server Errors
    public const INTERNAL_SERVER_ERROR = 500;
    public const BAD_GATEWAY = 502;
    public const SERVICE_UNAVAILABLE = 503;
    public const GATEWAY_TIMEOUT = 504;

    public const RETRYABLE_STATUS_CODES = [
        self::TOO_MANY_REQUESTS,
        self::INTERNAL_SERVER_ERROR,
        self::BAD_GATEWAY,
        self::SERVICE_UNAVAILABLE,
        self::GATEWAY_TIMEOUT,
    ];

    public const AUTHENTICATION_ERROR_STATUS_CODES = [
        self::UNAUTHORIZED,
        self::FORBIDDEN,
    ];
}
