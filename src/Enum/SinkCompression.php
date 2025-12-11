<?php

declare(strict_types=1);

namespace Brd6\TinybirdSdk\Enum;

/**
 * Compression formats supported by Tinybird.
 *
 * @see https://www.tinybird.co/docs/api-reference/events-api
 * @see https://www.tinybird.co/docs/api-reference/sink-pipes-api
 */
enum Compression: string
{
    case NONE = 'none';
    case GZIP = 'gzip';
    case GZ = 'gz';
    case ZSTD = 'zstd';
}
