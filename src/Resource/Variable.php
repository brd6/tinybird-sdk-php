<?php

declare(strict_types=1);

namespace Brd6\TinybirdSdk\Resource;

use Brd6\TinybirdSdk\Enum\VariableType;
use DateTimeImmutable;

/**
 * @see https://www.tinybird.co/docs/api-reference/environment-variables-api
 */
class Variable extends AbstractResource
{
    public string $name = '';
    public ?VariableType $type = null;
    public ?DateTimeImmutable $createdAt = null;
    public ?DateTimeImmutable $updatedAt = null;
    public ?string $editedBy = null;

    protected function initialize(): void
    {
        $data = $this->getRawData();

        $this->name = (string) ($data['name'] ?? '');
        $this->type = isset($data['type']) ? VariableType::tryFrom((string) $data['type']) : null;
        $this->editedBy = $data['edited_by'] ?? null;

        if (isset($data['created_at'])) {
            $this->createdAt = new DateTimeImmutable((string) $data['created_at']);
        }

        if (isset($data['updated_at'])) {
            $this->updatedAt = new DateTimeImmutable((string) $data['updated_at']);
        }
    }
}
