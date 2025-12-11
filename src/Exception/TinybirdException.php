<?php

declare(strict_types=1);

namespace Brd6\TinybirdSdk\Exception;

use Exception;

abstract class TinybirdException extends Exception
{
    abstract public function getErrorCode(): string;
}
