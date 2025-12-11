<?php

declare(strict_types=1);

namespace Brd6\TinybirdSdk\Endpoint;

use Brd6\TinybirdSdk\Constant\ContentType;
use Brd6\TinybirdSdk\Resource\AnalyzeResult;
use Brd6\TinybirdSdk\Util\NdjsonHelper;

use function json_decode;
use function str_starts_with;
use function strtok;
use function trim;

/**
 * Analyze API endpoint.
 *
 * Analyze NDJSON, CSV, or Parquet files to generate a Tinybird Data Source schema.
 *
 * @see https://www.tinybird.co/docs/api-reference/analyze-api
 */
class AnalyzeEndpoint extends AbstractEndpoint
{
    private const PATH = '/analyze';
    private const PARQUET_MAGIC = 'PAR1';

    /**
     * POST /v?/analyze
     *
     * Analyze file content to infer schema. Auto-detects content type.
     *
     * @param string $content Raw file content (NDJSON, CSV, or Parquet)
     * @param string|null $contentType Content type (auto-detected if null)
     */
    public function analyzeContent(string $content, ?string $contentType = null): AnalyzeResult
    {
        $contentType ??= $this->detectContentType($content);

        return AnalyzeResult::fromArray(
            $this->post(self::PATH, $content, [], ['Content-Type' => $contentType]),
        );
    }

    /**
     * POST /v?/analyze?url=...
     *
     * Analyze a remote file by URL.
     */
    public function analyzeUrl(string $url): AnalyzeResult
    {
        return AnalyzeResult::fromArray(
            $this->post(self::PATH, null, ['url' => $url]),
        );
    }

    /**
     * Analyze NDJSON records to infer schema.
     *
     * @param array<array<string, mixed>> $records Array of records
     */
    public function analyzeRecords(array $records): AnalyzeResult
    {
        return $this->analyzeContent(NdjsonHelper::encode($records), ContentType::NDJSON);
    }

    private function detectContentType(string $content): string
    {
        if (str_starts_with($content, self::PARQUET_MAGIC)) {
            return ContentType::PARQUET;
        }

        $firstLine = trim((string) strtok($content, "\n"));
        if ($firstLine !== '' && str_starts_with($firstLine, '{') && json_decode($firstLine) !== null) {
            return ContentType::NDJSON;
        }

        return ContentType::CSV;
    }
}
