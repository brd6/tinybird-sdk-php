<?php

declare(strict_types=1);

namespace Brd6\TinybirdSdk\Exception;

use Brd6\TinybirdSdk\Constant\TinybirdErrorCode;

class RequestTimeoutException extends TinybirdException
{
    public function __construct(string $message = 'Request timed out')
    {
        parent::__construct($message);
    }

    public function getErrorCode(): string
    {
        return TinybirdErrorCode::REQUEST_TIMEOUT;
    }
}
