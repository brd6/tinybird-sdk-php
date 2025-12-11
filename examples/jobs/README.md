# Jobs API Example

Demonstrates the Jobs API for monitoring and managing Tinybird background jobs.

## Features Covered

| Method | Description |
|--------|-------------|
| `list()` | List recent jobs (last 48h, max 100) |
| `list(params)` | Filter jobs by kind, status, pipe, date |
| `retrieve(id)` | Get detailed job information |
| `cancel(id)` | Cancel a waiting/running job |

## Setup

1. Copy environment file:

```bash
cp env.example .env
```

2. Edit `.env` with your Tinybird token:

```bash
TINYBIRD_TOKEN=p.eyJ...
TINYBIRD_LOCAL=false
```

3. Install dependencies:

```bash
composer install
```

4. Run the example:

```bash
php index.php
```

## Usage Examples

### List all jobs

```php
$jobs = $client->jobs()->list();
```

### Filter jobs

```php
use Brd6\TinybirdSdk\Enum\JobKind;
use Brd6\TinybirdSdk\Enum\JobStatus;
use Brd6\TinybirdSdk\RequestParameters\JobsListParams;

// By status
$doneJobs = $client->jobs()->list(new JobsListParams(
    status: JobStatus::DONE,
));

// By kind
$importJobs = $client->jobs()->list(new JobsListParams(
    kind: JobKind::IMPORT,
));

// By date range
$recentJobs = $client->jobs()->list(new JobsListParams(
    createdAfter: new DateTimeImmutable('-7 days'),
));

// Combined
$jobs = $client->jobs()->list(new JobsListParams(
    kind: JobKind::POPULATE_VIEW,
    status: JobStatus::WORKING,
    pipeName: 'my_pipe',
));
```

### Get job details

```php
$job = $client->jobs()->retrieve('job-id-here');

echo $job->status->value;  // 'done', 'working', 'waiting', 'error'
echo $job->kind->value;    // 'import', 'populateview', 'copy', etc.

// Helper methods
if ($job->isDone()) {
    echo "Job completed!";
}
```

### Cancel a job

```php
if ($job->isCancellable) {
    $cancelledJob = $client->jobs()->cancel($job->id);
}
```

## Job Statuses

| Status | Description |
|--------|-------------|
| `waiting` | Job is queued |
| `working` | Job is running |
| `done` | Job completed successfully |
| `error` | Job failed |
| `cancelling` | Cancellation in progress |
| `cancelled` | Job was cancelled |

## Job Kinds

| Kind | Description |
|------|-------------|
| `import` | Data import via URL |
| `populateview` | Materialized View populate |
| `copy` | Copy Pipe execution |
| `delete_data` | Row deletion |
| `query` | Background query |

