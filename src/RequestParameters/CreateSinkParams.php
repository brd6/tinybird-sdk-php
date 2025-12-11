<?php

declare(strict_types=1);

namespace Brd6\TinybirdSdk\RequestParameters;

use Brd6\TinybirdSdk\Enum\Compression;
use Brd6\TinybirdSdk\Enum\PipeFormat;
use Brd6\TinybirdSdk\Enum\SinkWriteStrategy;

/**
 * @see https://www.tinybird.co/docs/api-reference/sink-pipes-api
 */
class CreateSinkParams extends AbstractRequestParameters
{
    public function __construct(
        public string $connection,
        public string $path,
        public ?string $fileTemplate = null,
        public ?PipeFormat $format = null,
        public ?Compression $compression = null,
        public ?string $scheduleCron = null,
        public ?SinkWriteStrategy $writeStrategy = null,
    ) {
    }
}
