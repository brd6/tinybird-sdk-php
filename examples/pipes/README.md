# Pipes API Example

Demonstrates the Pipes API for listing, querying, and analyzing Tinybird Pipes.

## Features Covered

| Method | Description |
|--------|-------------|
| `list()` | List all Pipes in Workspace |
| `list(params)` | Filter response with attrs, dependencies |
| `retrieve(name)` | Get detailed Pipe information |
| `query(name, params)` | Query a published API Endpoint |
| `getData(name, format, params)` | Get data in various formats |
| `explain(name, nodeId?, params)` | Get query explain plan |

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

### List all Pipes

```php
$pipes = $client->pipes()->list();

foreach ($pipes as $pipe) {
    echo $pipe->name;
    if ($pipe->isApiEndpoint()) {
        echo " [ENDPOINT]";
    }
}
```

### List with filters

```php
use Brd6\TinybirdSdk\RequestParameters\PipesListParams;

// Limit attributes returned
$pipes = $client->pipes()->list(new PipesListParams(
    attrs: 'name,description,endpoint',
    nodeAttrs: 'id,name,sql',
));

// Include dependencies
$pipes = $client->pipes()->list(new PipesListParams(
    dependencies: true,
));
```

### Get Pipe details

```php
$pipe = $client->pipes()->retrieve('my_pipe');

echo $pipe->id;
echo $pipe->name;
echo $pipe->description;

// Access nodes
foreach ($pipe->nodes as $node) {
    echo $node->name;
    echo $node->sql;
    if ($node->isMaterialized()) {
        echo " [MATERIALIZED]";
    }
}

// Get endpoint node
$endpointNode = $pipe->getEndpointNode();
```

### Query a Pipe endpoint

```php
// Simple query
$result = $client->pipes()->query('my_endpoint');

echo $result->rows;
echo $result->getElapsedMs();

// With parameters
$result = $client->pipes()->query('my_endpoint', [
    'date_from' => '2024-01-01',
    'limit' => '100',
]);

// Custom SQL using _ shortcut
$result = $client->pipes()->query('my_endpoint', [
    'q' => 'SELECT count() FROM _ WHERE status = 1',
]);
```

### Get data in different formats

```php
use Brd6\TinybirdSdk\Enum\PipeFormat;

$json = $client->pipes()->getData('my_endpoint', PipeFormat::JSON);
$csv = $client->pipes()->getData('my_endpoint', PipeFormat::CSV);
$ndjson = $client->pipes()->getData('my_endpoint', PipeFormat::NDJSON);
$parquet = $client->pipes()->getData('my_endpoint', PipeFormat::PARQUET);
```

### Get explain plan

```php
// Explain default node
$explain = $client->pipes()->explain('my_pipe');
echo $explain->debugQuery;
echo $explain->queryExplain;

// Explain specific node
$explain = $client->pipes()->explain('my_pipe', 'node_id');

// Explain with test parameters
$explain = $client->pipes()->explain('my_pipe', null, [
    'status' => 'active',
]);
```

## Output Formats

| Format | Description |
|--------|-------------|
| `json` | JSON with data, statistics, and schema |
| `csv` | CSV without headers |
| `csvwithnames` | CSV with column headers |
| `ndjson` | Newline-delimited JSON |
| `parquet` | Apache Parquet file |
| `prometheus` | Prometheus text format |

## Pipe vs Node

A **Pipe** contains one or more **Nodes**:

```
Pipe: my_analytics
├── Node: my_analytics_0 (SELECT * FROM raw_events)
├── Node: my_analytics_1 (SELECT date, count() FROM my_analytics_0)
└── Node: my_analytics_2 [ENDPOINT] (SELECT * FROM my_analytics_1)
```

Use `pipe.getEndpointNode()` to get the published node, or `pipe.getNode('name')` to find a specific node.

