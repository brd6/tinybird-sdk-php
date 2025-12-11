<?php

declare(strict_types=1);

namespace Brd6\TinybirdSdk\Enum;

/**
 * @see https://www.tinybird.co/docs/api-reference/sink-pipes-api
 */
enum SinkWriteStrategy: string
{
    case NEW = 'new';
    case TRUNCATE = 'truncate';
}
