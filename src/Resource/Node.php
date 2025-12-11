<?php

declare(strict_types=1);

namespace Brd6\TinybirdSdk\Resource;

use DateTimeImmutable;

/**
 * @see https://www.tinybird.co/docs/api-reference/pipe-api
 */
class Node extends AbstractResource
{
    public string $id = '';
    public string $name = '';
    public string $sql = '';
    public ?string $description = null;
    public ?bool $materialized = null;
    public ?string $cluster = null;
    public ?string $nodeType = null;
    public ?DateTimeImmutable $createdAt = null;
    public ?DateTimeImmutable $updatedAt = null;
    public int $version = 0;
    public ?string $project = null;
    public ?string $result = null;
    public bool $ignoreSqlErrors = false;
    public ?Job $job = null;

    /** @var array<string, mixed> */
    public array $tags = [];

    /** @var array<string> */
    public array $dependencies = [];

    /** @var array<mixed> */
    public array $params = [];

    protected function initialize(): void
    {
        $data = $this->getRawData();

        $this->id = (string) ($data['id'] ?? '');
        $this->name = (string) ($data['name'] ?? '');
        $this->sql = (string) ($data['sql'] ?? '');
        $this->description = $data['description'] ?? null;
        $this->materialized = isset($data['materialized']) ? (bool) $data['materialized'] : null;
        $this->cluster = $data['cluster'] ?? null;
        $this->nodeType = $data['node_type'] ?? null;
        $this->version = (int) ($data['version'] ?? 0);
        $this->project = $data['project'] ?? null;
        $this->result = $data['result'] ?? null;
        $this->ignoreSqlErrors = (bool) ($data['ignore_sql_errors'] ?? false);
        $this->tags = (array) ($data['tags'] ?? []);
        $this->dependencies = (array) ($data['dependencies'] ?? []);
        $this->params = (array) ($data['params'] ?? []);

        if (isset($data['created_at'])) {
            $this->createdAt = new DateTimeImmutable((string) $data['created_at']);
        }

        if (isset($data['updated_at'])) {
            $this->updatedAt = new DateTimeImmutable((string) $data['updated_at']);
        }

        if (isset($data['job'])) {
            $this->job = Job::fromArray($data['job']);
        }
    }

    public function isMaterialized(): bool
    {
        return $this->materialized === true;
    }
}
