<?php

declare(strict_types=1);

namespace Brd6\TinybirdSdk\RequestParameters;

use Brd6\TinybirdSdk\Enum\JobKind;
use Brd6\TinybirdSdk\Enum\JobStatus;
use DateTimeInterface;

/**
 * @see https://www.tinybird.co/docs/api-reference/jobs-api
 */
class JobsListParams extends AbstractRequestParameters
{
    public function __construct(
        public JobKind|string|null $kind = null,
        public JobStatus|string|null $status = null,
        public ?string $pipeId = null,
        public ?string $pipeName = null,
        public DateTimeInterface|string|null $createdAfter = null,
        public DateTimeInterface|string|null $createdBefore = null,
    ) {
    }
}
