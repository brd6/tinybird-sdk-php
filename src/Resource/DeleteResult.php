<?php

declare(strict_types=1);

namespace Brd6\TinybirdSdk\Resource;

/**
 * @see https://www.tinybird.co/docs/api-reference
 */
class DeleteResult extends AbstractResource
{
    public bool $ok = false;

    protected function initialize(): void
    {
        $data = $this->getRawData();

        $this->ok = (bool) ($data['ok'] ?? false);
    }

    public function isSuccess(): bool
    {
        return $this->ok;
    }
}
