<?php

declare(strict_types=1);

namespace Brd6\TinybirdSdk\Exception;

use Brd6\TinybirdSdk\Constant\TinybirdErrorCode;

class ValidationException extends TinybirdException
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }

    public function getErrorCode(): string
    {
        return TinybirdErrorCode::VALIDATION_ERROR;
    }
}
