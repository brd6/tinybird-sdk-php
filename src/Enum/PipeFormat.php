<?php

declare(strict_types=1);

namespace Brd6\TinybirdSdk\Enum;

/**
 * @see https://www.tinybird.co/docs/api-reference/pipe-api
 */
enum PipeFormat: string
{
    case JSON = 'json';
    case CSV = 'csv';
    case CSV_WITH_NAMES = 'csvwithnames';
    case NDJSON = 'ndjson';
    case PARQUET = 'parquet';
    case PROMETHEUS = 'prometheus';
}
