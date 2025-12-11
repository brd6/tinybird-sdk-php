<?php

declare(strict_types=1);

namespace Brd6\TinybirdSdk\Endpoint;

use Brd6\TinybirdSdk\Enum\PipeFormat;
use Brd6\TinybirdSdk\RequestParameters\PipesListParams;
use Brd6\TinybirdSdk\Resource\ExplainResult;
use Brd6\TinybirdSdk\Resource\Pipe;
use Brd6\TinybirdSdk\Resource\QueryResult;

/**
 * Pipes API endpoint.
 *
 * List Pipes, retrieve details, query published endpoints, and get explain plans.
 * Write operations (create, update, delete) are managed via deployments in Forward workspaces.
 *
 * @see https://www.tinybird.co/docs/api-reference/pipe-api
 */
class PipesEndpoint extends AbstractEndpoint
{
    private const PATH = '/pipes';

    /**
     * GET /v?/pipes
     *
     * List all Pipes in the Workspace.
     *
     * @return array<Pipe>
     */
    public function list(?PipesListParams $params = null): array
    {
        $response = $this->get(self::PATH, $params?->toArray() ?? []);

        return array_map(
            static fn (array $pipe) => Pipe::fromArray($pipe),
            (array) ($response['pipes'] ?? []),
        );
    }

    /**
     * GET /v?/pipes/{name}
     *
     * Retrieve a Pipe by name or ID.
     */
    public function retrieve(string $name): Pipe
    {
        return Pipe::fromArray($this->get(self::PATH . '/' . $name));
    }

    /**
     * GET /v?/pipes/{name}.{format}
     *
     * Get published endpoint data in the specified format.
     * Use the `q` parameter for custom queries using `_` as a shortcut for the pipe name.
     *
     * @param array<string, mixed> $params Query parameters (e.g., endpoint params, q for custom query)
     */
    public function getData(string $name, PipeFormat $format = PipeFormat::JSON, array $params = []): QueryResult
    {
        return QueryResult::fromArray(
            $this->get(self::PATH . '/' . $name . '.' . $format->value, $params),
        );
    }

    /**
     * GET /v?/pipes/{name}.json
     *
     * Query a Pipe endpoint with parameters. Alias for getData with JSON format.
     *
     * @param array<string, mixed> $params Query parameters
     */
    public function query(string $name, array $params = []): QueryResult
    {
        return $this->getData($name, PipeFormat::JSON, $params);
    }

    /**
     * GET /v?/pipes/{name}/explain or GET /v?/pipes/{name}/nodes/{nodeId}/explain
     *
     * Get the explain plan and debug query for a Pipe or specific node.
     * Useful for understanding query execution and optimization.
     *
     * @param string $name Pipe name or ID
     * @param string|null $nodeId Optional node name or ID
     * @param array<string, mixed> $params Query parameters to test with
     */
    public function explain(string $name, ?string $nodeId = null, array $params = []): ExplainResult
    {
        $path = $nodeId !== null
            ? self::PATH . '/' . $name . '/nodes/' . $nodeId . '/explain'
            : self::PATH . '/' . $name . '/explain';

        return ExplainResult::fromArray($this->get($path, $params));
    }
}
