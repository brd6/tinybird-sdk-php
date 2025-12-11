<?php

declare(strict_types=1);

namespace Brd6\TinybirdSdk\Resource;

use Brd6\TinybirdSdk\Enum\JobKind;
use Brd6\TinybirdSdk\Enum\JobStatus;
use DateTimeImmutable;

/**
 * @see https://www.tinybird.co/docs/api-reference/jobs-api
 */
class Job extends AbstractResource
{
    public string $id = '';
    public ?JobKind $kind = null;
    public ?JobStatus $status = null;
    public ?DateTimeImmutable $createdAt = null;
    public ?DateTimeImmutable $updatedAt = null;
    public ?DateTimeImmutable $startedAt = null;
    public bool $isCancellable = false;
    public string $jobUrl = '';

    public ?string $mode = null;
    public ?string $url = null;
    public ?string $pipeId = null;
    public ?string $pipeName = null;
    public ?string $queryId = null;
    public ?string $importId = null;
    public ?string $deleteCondition = null;

    public ?Statistics $statistics = null;
    public int $quarantineRows = 0;
    public int $invalidLines = 0;
    public ?int $rowsAffected = null;
    public ?float $progressPercentage = null;

    /** @var array<string, mixed>|null */
    public ?array $datasource = null;

    /** @var array<array<string, mixed>> */
    public array $queries = [];

    protected function initialize(): void
    {
        $data = $this->getRawData();

        $this->id = (string) ($data['id'] ?? $data['job_id'] ?? '');
        $this->kind = isset($data['kind']) ? JobKind::tryFrom((string) $data['kind']) : null;
        $this->status = isset($data['status']) ? JobStatus::tryFrom((string) $data['status']) : null;
        $this->isCancellable = (bool) ($data['is_cancellable'] ?? false);
        $this->jobUrl = (string) ($data['job_url'] ?? '');

        $this->mode = isset($data['mode']) ? (string) $data['mode'] : null;
        $this->url = isset($data['url']) ? (string) $data['url'] : null;
        $this->pipeId = isset($data['pipe_id']) ? (string) $data['pipe_id'] : null;
        $this->pipeName = isset($data['pipe_name']) ? (string) $data['pipe_name'] : null;
        $this->queryId = isset($data['query_id']) ? (string) $data['query_id'] : null;
        $this->importId = isset($data['import_id']) ? (string) $data['import_id'] : null;
        $this->deleteCondition = isset($data['delete_condition']) ? (string) $data['delete_condition'] : null;

        $this->quarantineRows = (int) ($data['quarantine_rows'] ?? 0);
        $this->invalidLines = (int) ($data['invalid_lines'] ?? 0);
        $this->rowsAffected = isset($data['rows_affected']) ? (int) $data['rows_affected'] : null;
        $this->progressPercentage = isset($data['progress_percentage']) ? (float) $data['progress_percentage'] : null;

        $this->createdAt = $this->parseDateTime($data['created_at'] ?? null);
        $this->updatedAt = $this->parseDateTime($data['updated_at'] ?? null);
        $this->startedAt = $this->parseDateTime($data['started_at'] ?? null);

        if (isset($data['statistics'])) {
            $this->statistics = Statistics::fromArray((array) $data['statistics']);
        }

        $this->datasource = isset($data['datasource']) ? (array) $data['datasource'] : null;
        $this->queries = (array) ($data['queries'] ?? []);
    }

    private function parseDateTime(mixed $value): ?DateTimeImmutable
    {
        if ($value === null || $value === '') {
            return null;
        }

        $dateTime = DateTimeImmutable::createFromFormat('Y-m-d H:i:s.u', (string) $value);
        if ($dateTime === false) {
            $dateTime = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', (string) $value);
        }

        return $dateTime ?: null;
    }

    public function isDone(): bool
    {
        return $this->status === JobStatus::DONE;
    }

    public function isError(): bool
    {
        return $this->status === JobStatus::ERROR;
    }

    public function isWorking(): bool
    {
        return $this->status === JobStatus::WORKING;
    }

    public function isWaiting(): bool
    {
        return $this->status === JobStatus::WAITING;
    }

    public function isCancelled(): bool
    {
        return $this->status === JobStatus::CANCELLED || $this->status === JobStatus::CANCELLING;
    }

    public function getRowCount(): int
    {
        return $this->statistics?->rowCount ?? 0;
    }

    public function getBytes(): int
    {
        return $this->statistics?->bytes ?? 0;
    }
}
