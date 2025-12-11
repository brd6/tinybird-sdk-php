<?php

declare(strict_types=1);

namespace Brd6\TinybirdSdk\Endpoint;

use Brd6\TinybirdSdk\Constant\ContentType;
use Brd6\TinybirdSdk\RequestParameters\CreateSinkParams;
use Brd6\TinybirdSdk\RequestParameters\TriggerSinkParams;
use Brd6\TinybirdSdk\Resource\GcsCredentials;
use Brd6\TinybirdSdk\Resource\Node;
use Brd6\TinybirdSdk\Resource\Pipe;
use Brd6\TinybirdSdk\Resource\S3Settings;
use Brd6\TinybirdSdk\Util\UrlHelper;

/**
 * Sink Pipes API endpoint.
 *
 * Create, delete, schedule, and trigger Sink Pipes to export data to object stores.
 *
 * @see https://www.tinybird.co/docs/api-reference/sink-pipes-api
 */
class SinkPipesEndpoint extends AbstractEndpoint
{
    private const PATH_PIPES = '/pipes';
    private const PATH_S3_SETTINGS = '/integrations/s3/settings';
    private const PATH_S3_TRUST_POLICY = '/integrations/s3/policies/trust-policy';
    private const PATH_S3_WRITE_POLICY = '/integrations/s3/policies/write-access-policy';
    private const PATH_GCS_CREDENTIALS = '/datasources-bigquery-credentials';

    /**
     * POST /v?/pipes/{pipe_id}/nodes/{node_id}/sink
     *
     * Set a Pipe as a Sink Pipe, optionally scheduled.
     * Requires PIPES:CREATE token.
     */
    public function create(string $pipeId, string $nodeId, CreateSinkParams $params): Pipe
    {
        $path = self::PATH_PIPES . '/' . $pipeId . '/nodes/' . $nodeId . '/sink';

        return Pipe::fromArray(
            $this->post(
                $path,
                UrlHelper::buildQuery($params->toArray()),
                [],
                ['Content-Type' => ContentType::FORM_URLENCODED],
            ),
        );
    }

    /**
     * DELETE /v?/pipes/{pipe_id}/nodes/{node_id}/sink
     *
     * Remove the Sink configuration from a Pipe.
     * This doesn't delete the Pipe or Node, only the sink configuration.
     */
    public function remove(string $pipeId, string $nodeId): Pipe
    {
        $path = self::PATH_PIPES . '/' . $pipeId . '/nodes/' . $nodeId . '/sink';

        return Pipe::fromArray($this->delete($path));
    }

    /**
     * POST /v?/pipes/{pipe_id}/sink
     *
     * Trigger the Sink Pipe, creating a sink job.
     * Allows overriding some settings for this execution.
     * Requires PIPES:READ token.
     *
     * @return Node Node with job details attached
     */
    public function trigger(string $pipeId, ?TriggerSinkParams $params = null): Node
    {
        $path = self::PATH_PIPES . '/' . $pipeId . '/sink';
        $body = $params !== null ? UrlHelper::buildQuery($params->toArray()) : null;

        return Node::fromArray(
            $this->post(
                $path,
                $body,
                [],
                $body !== null ? ['Content-Type' => ContentType::FORM_URLENCODED] : [],
            ),
        );
    }

    /**
     * GET /v?/integrations/s3/settings
     *
     * Get S3 integration settings (principal ARN and external ID).
     * Requires ADMIN token.
     */
    public function getS3Settings(): S3Settings
    {
        return S3Settings::fromArray($this->get(self::PATH_S3_SETTINGS));
    }

    /**
     * GET /v?/integrations/s3/policies/trust-policy
     *
     * Get the IAM trust policy for S3 integration.
     * Requires ADMIN token.
     *
     * @return array<string, mixed>
     */
    public function getS3TrustPolicy(): array
    {
        return $this->get(self::PATH_S3_TRUST_POLICY);
    }

    /**
     * GET /v?/integrations/s3/policies/write-access-policy
     *
     * Get the IAM write access policy for S3 integration.
     * Requires ADMIN token.
     *
     * @return array<string, mixed>
     */
    public function getS3WriteAccessPolicy(?string $bucket = null): array
    {
        $query = $bucket !== null ? ['bucket' => $bucket] : [];

        return $this->get(self::PATH_S3_WRITE_POLICY, $query);
    }

    /**
     * GET /v?/datasources-bigquery-credentials
     *
     * Get GCP service account for GCS integration.
     * Requires ADMIN token.
     */
    public function getGcsCredentials(): GcsCredentials
    {
        return GcsCredentials::fromArray($this->get(self::PATH_GCS_CREDENTIALS));
    }
}
