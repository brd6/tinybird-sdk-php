<?php

declare(strict_types=1);

namespace Brd6\TinybirdSdk\Resource;

use DateTimeImmutable;

/**
 * Represents a Tinybird Pipe.
 *
 * @see https://www.tinybird.co/docs/api-reference/pipe-api
 */
class Pipe extends AbstractResource
{
    public string $id = '';
    public string $name = '';
    public ?string $description = null;
    public ?string $type = null;
    public ?string $endpoint = null;
    public ?string $parent = null;
    public ?string $url = null;
    public ?DateTimeImmutable $createdAt = null;
    public ?DateTimeImmutable $updatedAt = null;
    public ?string $publishedVersion = null;
    public ?DateTimeImmutable $publishedDate = null;
    public ?string $project = null;
    public ?string $sinkNode = null;

    /** @var array<string, mixed>|null */
    public ?array $schedule = null;

    /** @var array<string, mixed>|null */
    public ?array $lastCommit = null;

    /** @var array<string, mixed> */
    public array $tags = [];

    /** @var array<Node> */
    public array $nodes = [];

    protected function initialize(): void
    {
        $data = $this->getRawData();

        $this->id = (string) ($data['id'] ?? '');
        $this->name = (string) ($data['name'] ?? '');
        $this->description = $data['description'] ?? null;
        $this->type = $data['type'] ?? null;
        $this->endpoint = $data['endpoint'] ?? null;
        $this->parent = $data['parent'] ?? null;
        $this->url = $data['url'] ?? null;
        $this->publishedVersion = $data['published_version'] ?? null;
        $this->project = $data['project'] ?? null;
        $this->sinkNode = $data['sink_node'] ?? null;
        $this->schedule = $data['schedule'] ?? null;
        $this->lastCommit = $data['last_commit'] ?? null;
        $this->tags = (array) ($data['tags'] ?? []);

        if (isset($data['created_at'])) {
            $this->createdAt = new DateTimeImmutable((string) $data['created_at']);
        }

        if (isset($data['updated_at'])) {
            $this->updatedAt = new DateTimeImmutable((string) $data['updated_at']);
        }

        if (isset($data['published_date'])) {
            $this->publishedDate = new DateTimeImmutable((string) $data['published_date']);
        }

        $this->nodes = array_map(
            static fn (array $node) => Node::fromArray($node),
            (array) ($data['nodes'] ?? $data['pipeline']['nodes'] ?? []),
        );
    }

    public function isApiEndpoint(): bool
    {
        return $this->endpoint !== null;
    }

    public function getEndpointNode(): ?Node
    {
        if ($this->endpoint === null) {
            return null;
        }

        foreach ($this->nodes as $node) {
            if ($node->id === $this->endpoint) {
                return $node;
            }
        }

        return null;
    }

    public function getNode(string $nameOrId): ?Node
    {
        foreach ($this->nodes as $node) {
            if ($node->id === $nameOrId || $node->name === $nameOrId) {
                return $node;
            }
        }

        return null;
    }

    public function isSinkPipe(): bool
    {
        return $this->type === 'sink' || $this->sinkNode !== null;
    }

    public function getSinkNode(): ?Node
    {
        if ($this->sinkNode === null) {
            return null;
        }

        return $this->getNode($this->sinkNode);
    }
}
