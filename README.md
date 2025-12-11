<p align="center">
    <strong>Tinybird SDK for PHP</strong>
</p>

<p align="center">
    <a href="https://github.com/brd6/tinybird-sdk-php"><img src="https://img.shields.io/badge/source-brd6/tinybird--sdk--php-blue.svg?style=flat-square" alt="Source Code"></a>
    <a href="https://packagist.org/packages/brd6/tinybird-sdk-php"><img src="https://img.shields.io/packagist/v/brd6/tinybird-sdk-php.svg?style=flat-square&label=release" alt="Download Package"></a>
    <a href="https://php.net"><img src="https://img.shields.io/packagist/php-v/brd6/tinybird-sdk-php.svg?style=flat-square&colorB=%238892BF" alt="PHP Programming Language"></a>
    <a href="https://github.com/brd6/tinybird-sdk-php/blob/main/LICENSE"><img src="https://img.shields.io/packagist/l/brd6/tinybird-sdk-php.svg?style=flat-square&colorB=darkcyan" alt="Read License"></a>
    <a href="https://github.com/brd6/tinybird-sdk-php/actions/workflows/continuous-integration.yml"><img src="https://img.shields.io/github/actions/workflow/status/brd6/tinybird-sdk-php/.github/workflows/continuous-integration.yml?branch=main&style=flat-square&logo=github" alt="Build Status"></a>
</p>

PHP SDK for the [Tinybird API](https://www.tinybird.co) — the real-time data platform for developers. Build low-latency, high-concurrency analytics APIs over any data source in minutes. Ingest millions of events per second, query with SQL, and publish endpoints instantly.

## Synopsis

```php
// Ingest → Query → Results
$tinybird = Client::create('p.your_token');
$tinybird->events()->send('clicks', ['user' => 'alice', 'page' => '/home']);
$result = $tinybird->query()->sql('SELECT count() FROM clicks');
// → {"data":[{"count()":1}],"rows":1}
```

## Features

- **High-frequency ingestion** — Send millions of events per second via Events API
- **Schema generation** — Analyze files to generate Data Source schemas
- **Sub-second queries** — Execute SQL queries with instant results
- **Multi-region support** — EU, US, AWS, GCP, and custom deployments
- **Built-in retry logic** — Automatic retry with exponential backoff
- **PSR-18 compatible** — Works with Symfony, Guzzle, or any HTTP client
- **Type-safe resources** — Full PHP 8.1+ support with strict types

## Installation

Install this package using [Composer](https://getcomposer.org):

```bash
composer require brd6/tinybird-sdk-php
```

This package uses [PSR-18](https://www.php-fig.org/psr/psr-18/) HTTP client abstraction. You can use any compatible HTTP client. For quick setup with Symfony HTTP client:

```bash
composer require brd6/tinybird-sdk-php symfony/http-client nyholm/psr7
```

## Quick Start

```php
use Brd6\TinybirdSdk\Client;

// Create client (uses EU region by default)
$tinybird = Client::create('p.your_token_here');

// Ingest events
$tinybird->events()->send('events', [
    ['timestamp' => '2025-01-01 00:00:00', 'event' => 'page_view', 'user_id' => 'user_123'],
    ['timestamp' => '2025-01-01 00:00:01', 'event' => 'click', 'user_id' => 'user_456'],
]);

// Query with SQL
$result = $tinybird->query()->sql('SELECT count() FROM events');

// Call a Pipe endpoint
$result = $tinybird->pipes()->query('analytics_endpoint', ['date_from' => '2025-01-01']);
```

### Region Selection

```php
use Brd6\TinybirdSdk\Client;
use Brd6\TinybirdSdk\Enum\Region;

// US East
$tinybird = Client::forRegion('p.your_token', Region::GCP_US_EAST4);

// AWS EU Central
$tinybird = Client::forRegion('p.your_token', Region::AWS_EU_CENTRAL_1);

// Tinybird Local
$tinybird = Client::local('your_local_token');

// Custom port
$tinybird = Client::local('your_local_token', 8080);
```

### Custom Endpoint

For private deployments or custom domains:

```php
use Brd6\TinybirdSdk\Client;
use Brd6\TinybirdSdk\ClientOptions;

$options = (new ClientOptions())
    ->setToken('your_token')
    ->setBaseUrl('https://your-custom-endpoint.com');

$tinybird = new Client($options);
```

## Usage

### Analyze API

Analyze files to generate Tinybird Data Source schemas. Useful before creating `.datasource` files.

```php
// Analyze NDJSON records
$result = $tinybird->analyze()->analyzeRecords([
    ['timestamp' => '2025-01-01 00:00:00', 'event' => 'page_view', 'user_id' => 'user_123'],
    ['timestamp' => '2025-01-01 00:00:01', 'event' => 'click', 'user_id' => 'user_456'],
]);

// Get the generated schema (ready for .datasource files)
echo $result->getSchema();
// Output: timestamp DateTime `json:$.timestamp`, event String `json:$.event`, ...

// Inspect analyzed columns
foreach ($result->columns as $col) {
    echo "{$col->name}: {$col->recommendedType}";
    if ($col->presentPct < 1) {
        echo " (nullable)";
    }
}

// Analyze a remote file
$result = $tinybird->analyze()->analyzeUrl('https://example.com/data.ndjson');

// Analyze raw content
$result = $tinybird->analyze()->analyzeContent($csvContent);
```

### Events API

High-frequency data ingestion optimized for real-time analytics. This is the recommended way to ingest data in Tinybird Forward workspaces.

```php
// Send single event
$result = $tinybird->events()->send('events', [
    'timestamp' => '2025-01-01 00:00:00',
    'event' => 'page_view',
    'user_id' => 'user_123',
]);

// Send batch events
$result = $tinybird->events()->send('events', [
    ['timestamp' => '2025-01-01 00:00:00', 'event' => 'page_view', 'user_id' => 'user_123'],
    ['timestamp' => '2025-01-01 00:00:01', 'event' => 'click', 'user_id' => 'user_456'],
]);

// Check result
echo $result->successfulRows;   // Rows ingested
echo $result->quarantinedRows;  // Rows failed validation

// Send raw NDJSON
$tinybird->events()->sendRaw('events', $ndjsonString);
```

### Data Sources

Read Data Source information and metadata.

```php
// List all Data Sources
$datasources = $tinybird->dataSources()->list();

foreach ($datasources as $ds) {
    echo $ds->name;
    echo $ds->getRowCount();
    echo $ds->getBytes();
}

// Get Data Source details
$info = $tinybird->dataSources()->retrieve('events');

echo $info->id;
echo $info->name;
echo $info->type;
echo $info->createdAt;

// Access columns
foreach ($info->columns as $column) {
    echo $column->name;
    echo $column->type;
}

// Get quarantine data
$quarantine = $tinybird->dataSources()->quarantine('events');
```

> **Note:** For schema changes, use the Tinybird CLI (`tb deploy`). See [Tinybird Forward documentation](https://www.tinybird.co/docs/forward).

### Query API

Execute raw SQL queries against your Data Sources.

```php
// Simple query
$result = $tinybird->query()->sql('SELECT count() FROM events');

echo $result->rows;
echo $result->data[0]['count()'];

// Query with parameters
$result = $tinybird->query()->sql(
    'SELECT * FROM events WHERE user_id = {user_id:String} LIMIT {limit:Int32}',
    ['user_id' => 'user_123', 'limit' => 100]
);

// Access results
foreach ($result->data as $row) {
    echo $row['event'];
}

// Query statistics
echo $result->getElapsedTime();
echo $result->getRowsRead();
echo $result->getBytesRead();
```

### Pipes API

Query published API Endpoints and list Pipes.

```php
// List Pipes
$pipes = $tinybird->pipes()->list();

// Query a Pipe endpoint
$result = $tinybird->pipes()->query('my_analytics_endpoint', [
    'start_date' => '2025-01-01',
    'end_date' => '2025-12-31',
    'limit' => 1000,
]);

foreach ($result->data as $row) {
    // Process results
}
```

### Jobs API

Monitor and manage background jobs (imports, populates, copies).

```php
use Brd6\TinybirdSdk\RequestParameters\ListJobsParams;
use Brd6\TinybirdSdk\Enum\JobStatus;

// List recent jobs
$jobs = $tinybird->jobs()->list();

// Filter by status
$jobs = $tinybird->jobs()->list(new ListJobsParams(status: JobStatus::DONE));

// Get job details
$job = $tinybird->jobs()->retrieve($jobId);
echo $job->status; // waiting, working, done, error

// Cancel a running job
$tinybird->jobs()->cancel($jobId);
```

### Environment Variables API

Manage workspace variables for use in Pipes.

```php
// List all variables
$vars = $tinybird->variables()->list();

// Create a variable
$var = $tinybird->variables()->create('API_KEY', 'secret_value');

// Update a variable
$tinybird->variables()->update('API_KEY', 'new_value');

// Delete a variable
$tinybird->variables()->remove('API_KEY');
```

### Tokens API

Manage workspace tokens for authentication.

```php
use Brd6\TinybirdSdk\RequestParameters\CreateTokenParams;

// List all tokens
$tokens = $tinybird->tokens()->list();

// Create a token with scopes
$token = $tinybird->tokens()->create(new CreateTokenParams(
    name: 'my_token',
    scopes: ['PIPES:READ', 'DATASOURCES:READ'],
));

// Refresh (rotate) a token
$newToken = $tinybird->tokens()->refresh('my_token');

// Delete a token
$tinybird->tokens()->remove('my_token');
```

### Sink Pipes API

Export data to object stores (S3, GCS).

```php
use Brd6\TinybirdSdk\RequestParameters\CreateSinkParams;

// Create a sink pipe
$pipe = $tinybird->sinkPipes()->create($pipeId, $nodeId, new CreateSinkParams(
    connection: 's3://bucket/path',
    path: 'exports/',
));

// Trigger a sink export
$result = $tinybird->sinkPipes()->trigger($pipeId);

// Get S3 integration settings
$settings = $tinybird->sinkPipes()->getS3Settings();
```

### Error Handling

```php
use Brd6\TinybirdSdk\Exception\ApiException;
use Brd6\TinybirdSdk\Exception\AuthenticationException;
use Brd6\TinybirdSdk\Exception\RateLimitException;
use Brd6\TinybirdSdk\Exception\RequestTimeoutException;

try {
    $result = $tinybird->pipes()->query('my_endpoint');
} catch (AuthenticationException $e) {
    // Invalid or expired token (401/403)
    // Includes helpful suggestions for region mismatches
} catch (RateLimitException $e) {
    // Too many requests (429)
    $retryAfter = $e->getRetryAfter();
    $limit = $e->getRateLimitLimit();
    $remaining = $e->getRateLimitRemaining();
} catch (RequestTimeoutException $e) {
    // Request timed out
} catch (ApiException $e) {
    // Other API errors
    $code = $e->getCode();
    $message = $e->getMessage();
    $response = $e->getResponse();
}
```

## Regions

| Region | Enum | API Base URL |
|--------|------|--------------|
| GCP Europe West 3 (default) | `Region::GCP_EUROPE_WEST3` | `https://api.tinybird.co` |
| GCP Europe West 2 | `Region::GCP_EUROPE_WEST2` | `https://api.europe-west2.gcp.tinybird.co` |
| GCP US East 4 | `Region::GCP_US_EAST4` | `https://api.us-east.tinybird.co` |
| GCP North America | `Region::GCP_NORTHAMERICA_NORTHEAST2` | `https://api.northamerica-northeast2.gcp.tinybird.co` |
| AWS EU Central 1 | `Region::AWS_EU_CENTRAL_1` | `https://api.eu-central-1.aws.tinybird.co` |
| AWS EU West 1 | `Region::AWS_EU_WEST_1` | `https://api.eu-west-1.aws.tinybird.co` |
| AWS US East 1 | `Region::AWS_US_EAST_1` | `https://api.us-east.aws.tinybird.co` |
| AWS US West 2 | `Region::AWS_US_WEST_2` | `https://api.us-west-2.aws.tinybird.co` |
| Local | `Region::LOCAL` | `http://localhost:7181` |

## Configuration

```php
use Brd6\TinybirdSdk\Client;
use Brd6\TinybirdSdk\ClientOptions;
use Brd6\TinybirdSdk\Enum\Region;

$options = (new ClientOptions())
    ->setToken(getenv('TINYBIRD_TOKEN'))
    ->setRegion(Region::AWS_EU_CENTRAL_1)
    ->setTimeout(120)
    ->setCompression(true)
    ->setRetryMaxRetries(5)
    ->setRetryDelayMs(1000)
    ->setRetryBackoffMultiplier(3);

$tinybird = new Client($options);
```

| Option | Default | Description |
|--------|---------|-------------|
| `token` | `''` | Tinybird API token |
| `region` | `Region::GCP_EUROPE_WEST3` | API region |
| `baseUrl` | `https://api.tinybird.co` | Custom API endpoint |
| `timeout` | `60` | Request timeout in seconds |
| `compression` | `false` | Enable gzip compression |
| `apiVersion` | `v0` | API version |
| `httpClient` | auto-discovered | Custom PSR-18 HTTP client |
| `retryMaxRetries` | `3` | Maximum retry attempts |
| `retryDelayMs` | `2000` | Initial retry delay (ms) |
| `retryBackoffMultiplier` | `2` | Backoff multiplier |

## API Reference

| Endpoint | Methods |
|----------|---------|
| `analyze()` | `analyzeContent()`, `analyzeRecords()`, `analyzeUrl()` |
| `events()` | `send()`, `sendRaw()`, `sendJson()` |
| `dataSources()` | `list()`, `retrieve()`, `quarantine()` |
| `query()` | `sql()` |
| `pipes()` | `list()`, `retrieve()`, `query()`, `getData()`, `explain()` |
| `jobs()` | `list()`, `retrieve()`, `cancel()` |
| `variables()` | `list()`, `retrieve()`, `create()`, `update()`, `remove()` |
| `tokens()` | `list()`, `retrieve()`, `create()`, `createJwt()`, `update()`, `refresh()`, `remove()` |
| `sinkPipes()` | `create()`, `remove()`, `trigger()`, `getS3Settings()`, `getGcsCredentials()` |

## Examples

See the [examples](./examples) directory for complete working examples:

- [Quick Start](./examples/quick-start) — Basic SDK usage
- [Analyze API](./examples/analyze) — Schema inference
- [Data Sources](./examples/datasources) — List and inspect Data Sources
- [Events API](./examples/events) — High-frequency ingestion
- [Jobs API](./examples/jobs) — Monitor background jobs
- [Pipes API](./examples/pipes) — Query API Endpoints
- [Tokens API](./examples/tokens) — Token management
- [Variables API](./examples/variables) — Environment variables

## Documentation

- [Tinybird API Reference](https://www.tinybird.co/docs/api-reference)
- [Tinybird Forward Documentation](https://www.tinybird.co/docs/forward)

## Contributing

Contributions are welcome! See [CONTRIBUTING.md](CONTRIBUTING.md).
