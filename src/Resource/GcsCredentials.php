<?php

declare(strict_types=1);

namespace Brd6\TinybirdSdk\Resource;

/**
 * @see https://www.tinybird.co/docs/api-reference/sink-pipes-api
 */
class GcsCredentials extends AbstractResource
{
    public string $account = '';

    protected function initialize(): void
    {
        $data = $this->getRawData();

        $this->account = (string) ($data['account'] ?? '');
    }
}
