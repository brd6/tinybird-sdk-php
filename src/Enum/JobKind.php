<?php

declare(strict_types=1);

namespace Brd6\TinybirdSdk\Enum;

/**
 * Job kind/type values.
 *
 * @see https://www.tinybird.co/docs/api-reference/jobs-api
 */
enum JobKind: string
{
    case IMPORT = 'import';
    case POPULATE_VIEW = 'populateview';
    case COPY = 'copy';
    case DELETE_DATA = 'delete_data';
    case QUERY = 'query';
}
