<?php

declare(strict_types=1);

namespace Brd6\TinybirdSdk\Enum;

/**
 * @see https://www.tinybird.co/docs/api-reference/environment-variables-api
 */
enum VariableType: string
{
    case SECRET = 'secret';
}
