<?php

declare(strict_types=1);

namespace Brd6\TinybirdSdk\Endpoint;

use Brd6\TinybirdSdk\Constant\ContentType;
use Brd6\TinybirdSdk\Enum\VariableType;
use Brd6\TinybirdSdk\Resource\DeleteResult;
use Brd6\TinybirdSdk\Resource\Variable;
use Brd6\TinybirdSdk\Resource\VariablesList;
use Brd6\TinybirdSdk\Util\UrlHelper;

/**
 * Environment Variables API endpoint.
 *
 * Create, update, delete, and list environment variables for use in Pipes.
 * Requires a Workspace admin token.
 *
 * @see https://www.tinybird.co/docs/api-reference/environment-variables-api
 */
class VariablesEndpoint extends AbstractEndpoint
{
    private const PATH = '/variables';

    /**
     * GET /v?/variables
     *
     * List all environment variables in the Workspace.
     * Note: Values are not returned for security.
     */
    public function list(): VariablesList
    {
        return VariablesList::fromArray($this->get(self::PATH));
    }

    /**
     * GET /v?/variables/{name}
     *
     * Retrieve a specific environment variable.
     * Note: Value is not returned for security.
     */
    public function retrieve(string $name): Variable
    {
        return Variable::fromArray($this->get(self::PATH . '/' . $name));
    }

    /**
     * POST /v?/variables
     *
     * Create a new environment variable.
     */
    public function create(string $name, string $value, VariableType $type = VariableType::SECRET): Variable
    {
        return Variable::fromArray(
            $this->post(
                self::PATH,
                UrlHelper::buildQuery(['name' => $name, 'value' => $value, 'type' => $type->value]),
                [],
                ['Content-Type' => ContentType::FORM_URLENCODED],
            ),
        );
    }

    /**
     * PUT /v?/variables/{name}
     *
     * Update an existing environment variable.
     */
    public function update(string $name, string $value): Variable
    {
        return Variable::fromArray(
            $this->put(
                self::PATH . '/' . $name,
                UrlHelper::buildQuery(['value' => $value]),
                [],
                ['Content-Type' => ContentType::FORM_URLENCODED],
            ),
        );
    }

    /**
     * DELETE /v?/variables/{name}
     *
     * Delete an environment variable.
     */
    public function remove(string $name): DeleteResult
    {
        return DeleteResult::fromArray($this->delete(self::PATH . '/' . $name));
    }
}
