<?php

declare(strict_types=1);

namespace Brd6\TinybirdSdk\RequestParameters;

/**
 * @see https://www.tinybird.co/docs/api-reference/pipe-api
 */
class PipesListParams extends AbstractRequestParameters
{
    public function __construct(
        public ?bool $dependencies = null,
        public ?string $attrs = null,
        public ?string $nodeAttrs = null,
    ) {
    }
}
