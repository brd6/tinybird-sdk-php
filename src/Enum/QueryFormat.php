<?php

declare(strict_types=1);

namespace Brd6\TinybirdSdk\Enum;

/**
 * @see https://www.tinybird.co/docs/api-reference/query-api
 */
enum QueryFormat: string
{
    case CSV = 'CSV';
    case CSV_WITH_NAMES = 'CSVWithNames';
    case JSON = 'JSON';
    case TSV = 'TSV';
    case TSV_WITH_NAMES = 'TSVWithNames';
    case PRETTY_COMPACT = 'PrettyCompact';
    case JSON_EACH_ROW = 'JSONEachRow';
    case PARQUET = 'Parquet';
    case PROMETHEUS = 'Prometheus';
}
