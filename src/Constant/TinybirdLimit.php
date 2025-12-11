<?php

declare(strict_types=1);

namespace Brd6\TinybirdSdk\Constant;

/**
 * @see https://www.tinybird.co/docs/forward/pricing/limits
 */
final class TinybirdLimit
{
    private const KB = 1024;
    private const MB = self::KB ** 2;

    // Ingestion limits
    public const EVENTS_API_REQUEST_SIZE_BYTES = 10 * self::MB;
    public const EVENTS_API_REQUESTS_PER_SECOND = 100;
    public const DATASOURCE_MAX_COLUMNS = 500;
    public const FULL_BODY_UPLOAD_BYTES = 8 * self::MB;
    public const MULTIPART_UPLOAD_CSV_NDJSON_BYTES = 500 * self::MB;
    public const MULTIPART_UPLOAD_PARQUET_BYTES = 50 * self::MB;

    // Query limits
    public const SQL_LENGTH_BYTES = 8 * self::KB;
    public const RESULT_LENGTH_BYTES = 100 * self::MB;
    public const QUERY_EXECUTION_TIME_SECONDS = 10;

    // Response limits
    public const RESPONSE_SIZE_BYTES = 100 * self::MB;
}
