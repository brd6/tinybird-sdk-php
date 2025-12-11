<?php

declare(strict_types=1);

namespace Brd6\TinybirdSdk\Resource;

/**
 * @see https://www.tinybird.co/docs/api-reference/pipe-api
 */
class ExplainResult extends AbstractResource
{
    public string $debugQuery = '';
    public string $queryExplain = '';

    protected function initialize(): void
    {
        $data = $this->getRawData();

        $this->debugQuery = (string) ($data['debug_query'] ?? '');
        $this->queryExplain = (string) ($data['query_explain'] ?? '');
    }
}
