<?php

declare(strict_types=1);

namespace Brd6\TinybirdSdk\Endpoint;

use Brd6\TinybirdSdk\Constant\ContentType;
use Brd6\TinybirdSdk\Enum\QueryFormat;
use Brd6\TinybirdSdk\RequestParameters\QueryParams;
use Brd6\TinybirdSdk\Resource\QueryResult;

use function rtrim;
use function sprintf;
use function stripos;
use function trim;

/**
 * Query API endpoint for executing raw SQL.
 *
 * The SDK automatically appends `FORMAT JSON` to queries for proper parsing.
 * If you need a different format, specify it in your query or use the `$format` parameter.
 *
 * @see https://www.tinybird.co/docs/api-reference/query-api
 */
class QueryEndpoint extends AbstractEndpoint
{
    private const PATH = '/sql';

    /**
     * GET /v?/sql?q={query}
     *
     * Execute a SQL query.
     *
     * @param array<string, mixed> $params Additional query parameters
     */
    public function sql(
        string $query,
        QueryFormat $format = QueryFormat::JSON,
        ?QueryParams $options = null,
        array $params = [],
    ): QueryResult {
        $allParams = [
            'q' => $this->formatQuery($query, $format),
            ...($options?->toArray() ?? []),
            ...$params,
        ];

        return QueryResult::fromArray($this->get(self::PATH, $allParams));
    }

    /**
     * POST /v?/sql
     *
     * Execute a templated SQL query with parameters.
     * Use `{{Type(param_name)}}` syntax for templates.
     *
     * @param string $query SQL query (prefix with `%` for templated queries)
     * @param array<string, mixed> $templateParams Template parameter values
     */
    public function sqlPost(
        string $query,
        array $templateParams = [],
        QueryFormat $format = QueryFormat::JSON,
        ?QueryParams $options = null,
    ): QueryResult {
        $body = [
            'q' => $this->formatQuery($query, $format),
            ...($options?->toArray() ?? []),
            ...$templateParams,
        ];

        return QueryResult::fromArray(
            $this->post(self::PATH, $body, [], ['Content-Type' => ContentType::JSON]),
        );
    }

    /**
     * Execute SQL query using pipeline placeholder.
     *
     * Use `_` in your query as a placeholder for the pipe name.
     *
     * @param string $query SQL query with `_` placeholder (e.g., "SELECT * FROM _")
     * @param string $pipeline Pipe name to substitute for `_`
     * @param array<string, mixed> $params Additional query parameters
     */
    public function sqlPipeline(
        string $query,
        string $pipeline,
        QueryFormat $format = QueryFormat::JSON,
        array $params = [],
    ): QueryResult {
        return $this->sql($query, $format, new QueryParams(pipeline: $pipeline), $params);
    }

    private function formatQuery(string $query, QueryFormat $format = QueryFormat::JSON): string
    {
        $query = rtrim(trim($query), ';');

        if (stripos($query, ' FORMAT ') !== false) {
            return $query;
        }

        return sprintf('%s FORMAT %s', $query, $format->value);
    }
}
