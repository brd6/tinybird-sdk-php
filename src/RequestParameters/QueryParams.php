<?php

declare(strict_types=1);

namespace Brd6\TinybirdSdk\RequestParameters;

/**
 * @see https://www.tinybird.co/docs/api-reference/query-api
 */
class QueryParams extends AbstractRequestParameters
{
    public function __construct(
        public ?string $pipeline = null,
        public ?int $outputFormatJsonQuote64bitIntegers = null,
        public ?int $outputFormatJsonQuoteDenormals = null,
        public ?int $outputFormatParquetStringAsString = null,
    ) {
    }
}
