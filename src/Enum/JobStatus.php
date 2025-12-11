<?php

declare(strict_types=1);

namespace Brd6\TinybirdSdk\Enum;

/**
 * Job status values.
 *
 * @see https://www.tinybird.co/docs/api-reference/jobs-api
 */
enum JobStatus: string
{
    case WAITING = 'waiting';
    case WORKING = 'working';
    case DONE = 'done';
    case ERROR = 'error';
    case CANCELLING = 'cancelling';
    case CANCELLED = 'cancelled';
}
