<?php

declare(strict_types=1);

namespace Brd6\TinybirdSdk\RequestParameters;

use Brd6\TinybirdSdk\Enum\Compression;
use Brd6\TinybirdSdk\Enum\PipeFormat;
use Brd6\TinybirdSdk\Enum\SinkWriteStrategy;

/**
 * @see https://www.tinybird.co/docs/api-reference/sink-pipes-api
 */
class TriggerSinkParams extends AbstractRequestParameters
{
    /**
     * @param array<string, string> $templateVariables Additional variables for file template
     */
    public function __construct(
        public ?string $connection = null,
        public ?string $path = null,
        public ?string $fileTemplate = null,
        public ?PipeFormat $format = null,
        public ?Compression $compression = null,
        public ?SinkWriteStrategy $writeStrategy = null,
        public array $templateVariables = [],
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        $params = parent::jsonSerialize();

        if (isset($params['template_variables'])) {
            $vars = $params['template_variables'];
            unset($params['template_variables']);

            return [...$params, ...$vars];
        }

        return $params;
    }
}
