<?php

declare(strict_types=1);

namespace Brd6\TinybirdSdk\Resource;

/**
 * @see https://www.tinybird.co/docs/api-reference/sink-pipes-api
 */
class S3Settings extends AbstractResource
{
    public string $principal = '';
    public string $externalId = '';

    protected function initialize(): void
    {
        $data = $this->getRawData();

        $this->principal = (string) ($data['principal'] ?? '');
        $this->externalId = (string) ($data['external_id'] ?? '');
    }
}
