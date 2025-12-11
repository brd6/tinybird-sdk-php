<?php

declare(strict_types=1);

namespace Brd6\TinybirdSdk\Endpoint;

use Brd6\TinybirdSdk\Resource\DataSource;

/**
 * Data Sources API endpoint.
 *
 * Provides read access to Data Sources.
 * For data ingestion, use the Events API.
 * For schema changes, use the Tinybird CLI (tb deploy).
 *
 * @see https://www.tinybird.co/docs/api-reference/datasource-api
 */
class DataSourcesEndpoint extends AbstractEndpoint
{
    private const PATH = '/datasources';

    /**
     * GET /v?/datasources
     *
     * List all Data Sources in the Workspace.
     *
     * @param string|null $attrs Comma-separated list of attributes to return
     * @return array<DataSource>
     */
    public function list(?string $attrs = null): array
    {
        $params = [];
        if ($attrs !== null) {
            $params['attrs'] = $attrs;
        }

        $response = $this->get(self::PATH, $params);

        return array_map(
            static fn (array $ds) => DataSource::fromArray($ds),
            (array) ($response['datasources'] ?? []),
        );
    }

    /**
     * GET /v?/datasources/{name}
     *
     * Retrieve details of a specific Data Source.
     */
    public function retrieve(string $name): DataSource
    {
        return DataSource::fromArray(
            $this->get(self::PATH . '/' . $name),
        );
    }
}
