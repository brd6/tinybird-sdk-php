<?php

declare(strict_types=1);

namespace Brd6\TinybirdSdk\Resource;

class UsedBy extends AbstractResource
{
    public string $id = '';
    public string $name = '';

    protected function initialize(): void
    {
        $data = $this->getRawData();

        $this->id = (string) ($data['id'] ?? '');
        $this->name = (string) ($data['name'] ?? '');
    }
}
