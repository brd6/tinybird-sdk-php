<?php

declare(strict_types=1);

namespace Brd6\TinybirdSdk\Endpoint;

use Brd6\TinybirdSdk\Constant\ContentType;
use Brd6\TinybirdSdk\Resource\IngestResult;
use Brd6\TinybirdSdk\Util\NdjsonHelper;

use const JSON_THROW_ON_ERROR;

/**
 * Events API endpoint.
 *
 * Ingest JSON/NDJSON events with a simple HTTP POST request.
 * Requires a Token with DATASOURCE:APPEND or DATASOURCE:CREATE scope.
 *
 * @see https://www.tinybird.co/docs/api-reference/events-api
 */
class EventsEndpoint extends AbstractEndpoint
{
    private const PATH = '/events';

    /**
     * POST /v?/events?name={datasource}
     *
     * Send events to a Data Source as NDJSON.
     *
     * @param string $datasource Name or ID of the target Data Source
     * @param array<string, mixed>|array<array<string, mixed>> $events Single event or array of events
     * @param bool $wait Wait for write acknowledgment (adds latency but enables retry on DB errors)
     */
    public function send(string $datasource, array $events, bool $wait = false): IngestResult
    {
        return $this->sendNdjson($datasource, NdjsonHelper::encode($events), $wait);
    }

    /**
     * POST /v?/events?name={datasource}&format=json
     *
     * Send a single JSON event to a Data Source.
     *
     * @param string $datasource Name or ID of the target Data Source
     * @param array<string, mixed> $event Single event
     * @param bool $wait Wait for write acknowledgment
     */
    public function sendJson(string $datasource, array $event, bool $wait = false): IngestResult
    {
        return IngestResult::fromArray(
            $this->post(
                self::PATH,
                json_encode($event, JSON_THROW_ON_ERROR),
                $this->buildParams($datasource, $wait, 'json'),
                ['Content-Type' => ContentType::JSON],
            ),
        );
    }

    /**
     * POST /v?/events?name={datasource}
     *
     * Send raw NDJSON data to a Data Source.
     *
     * @param string $datasource Name or ID of the target Data Source
     * @param string $ndjson Raw NDJSON content
     * @param bool $wait Wait for write acknowledgment
     */
    public function sendNdjson(string $datasource, string $ndjson, bool $wait = false): IngestResult
    {
        return IngestResult::fromArray(
            $this->post(
                self::PATH,
                $ndjson,
                $this->buildParams($datasource, $wait),
                ['Content-Type' => ContentType::NDJSON],
            ),
        );
    }

    /**
     * POST /v?/events?name={datasource} with Content-Encoding: gzip
     *
     * Send gzip-compressed NDJSON data to a Data Source.
     *
     * @param string $datasource Name or ID of the target Data Source
     * @param string $compressedData Gzip-compressed NDJSON content
     * @param bool $wait Wait for write acknowledgment
     */
    public function sendGzip(string $datasource, string $compressedData, bool $wait = false): IngestResult
    {
        return IngestResult::fromArray(
            $this->post(
                self::PATH,
                $compressedData,
                $this->buildParams($datasource, $wait),
                ['Content-Type' => ContentType::NDJSON, 'Content-Encoding' => 'gzip'],
            ),
        );
    }

    /**
     * POST /v?/events?name={datasource} with Content-Encoding: zstd
     *
     * Send zstd-compressed NDJSON data to a Data Source.
     *
     * @param string $datasource Name or ID of the target Data Source
     * @param string $compressedData Zstandard-compressed NDJSON content
     * @param bool $wait Wait for write acknowledgment
     */
    public function sendZstd(string $datasource, string $compressedData, bool $wait = false): IngestResult
    {
        return IngestResult::fromArray(
            $this->post(
                self::PATH,
                $compressedData,
                $this->buildParams($datasource, $wait),
                ['Content-Type' => ContentType::NDJSON, 'Content-Encoding' => 'zstd'],
            ),
        );
    }

    /**
     * @return array<string, string>
     */
    private function buildParams(string $datasource, bool $wait, ?string $format = null): array
    {
        $params = ['name' => $datasource];

        if ($wait) {
            $params['wait'] = 'true';
        }

        if ($format !== null) {
            $params['format'] = $format;
        }

        return $params;
    }
}
