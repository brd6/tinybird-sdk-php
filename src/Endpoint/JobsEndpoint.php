<?php

declare(strict_types=1);

namespace Brd6\TinybirdSdk\Endpoint;

use Brd6\TinybirdSdk\RequestParameters\JobsListParams;
use Brd6\TinybirdSdk\Resource\Job;

/**
 * Jobs API endpoint.
 *
 * List jobs for the last 48 hours (max 100), get job details, or cancel jobs.
 *
 * @see https://www.tinybird.co/docs/api-reference/jobs-api
 */
class JobsEndpoint extends AbstractEndpoint
{
    private const PATH = '/jobs';

    /**
     * GET /v?/jobs
     *
     * List jobs (last 48 hours, max 100).
     *
     * @return array<Job>
     */
    public function list(?JobsListParams $params = null): array
    {
        $response = $this->get(self::PATH, $params?->toArray() ?? []);

        return array_map(
            static fn (array $job) => Job::fromArray($job),
            (array) ($response['jobs'] ?? []),
        );
    }

    /**
     * GET /v?/jobs/{id}
     *
     * Get job details. Available for 48 hours after job creation.
     */
    public function retrieve(string $jobId): Job
    {
        return Job::fromArray(
            $this->get(self::PATH . '/' . $jobId),
        );
    }

    /**
     * POST /v?/jobs/{id}/cancel
     *
     * Cancel a job. Jobs in "waiting" status can always be cancelled.
     * Populate jobs can also be cancelled in "working" status.
     */
    public function cancel(string $jobId): Job
    {
        return Job::fromArray(
            $this->post(self::PATH . '/' . $jobId . '/cancel'),
        );
    }
}
