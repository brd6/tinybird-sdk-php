# Events API Example

Demonstrates all Events API features for ingesting data into Tinybird.

## Features Covered

| Method | Description |
|--------|-------------|
| `send()` | Send array of events as NDJSON (most common) |
| `sendJson()` | Send a single JSON event |
| `sendNdjson()` | Send raw NDJSON string |
| `sendGzip()` | Send gzip-compressed NDJSON |
| `sendZstd()` | Send zstd-compressed NDJSON |
| `wait` parameter | Wait for write acknowledgment |

## Setup

1. Copy environment file:

```bash
cp env.example .env
```

2. Edit `.env` with your Tinybird token and Data Source name:

```bash
TINYBIRD_TOKEN=p.eyJ...
TINYBIRD_DATASOURCE=your_events_datasource
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

## Data Source Schema

The example expects a Data Source with this schema:

```sql
SCHEMA >
    `timestamp` DateTime `json:$.timestamp`,
    `event` String `json:$.event`,
    `user_id` String `json:$.user_id`,
    `properties` String `json:$.properties`
```

You can create it via `.datasource` file and deploy with `tb deploy`.

## Example Output

```
============================================================
  Tinybird SDK PHP - Events API Examples
============================================================
[INFO] Connecting to Tinybird Cloud (gcp-europe-west3)
[INFO] Target Data Source: sdk_test_events

------------------------------------------------------------
  1. send() - Send events as array
------------------------------------------------------------

[INFO] Sending multiple events...
   Successful rows: 2
   Quarantined rows: 0
[OK] Events sent as NDJSON

------------------------------------------------------------
  2. sendJson() - Send single JSON event
------------------------------------------------------------

[INFO] Sending single event with format=json...
   Successful rows: 1
[OK] Single JSON event sent

...
```

## Compression

For large payloads, use compression to reduce bandwidth:

```php
$ndjson = NdjsonHelper::encode($events);
$compressed = gzencode($ndjson, 9);
$client->events()->sendGzip($datasource, $compressed);
```

## Wait Parameter

By default, the Events API returns immediately (HTTP 202). Use `wait: true` for acknowledged writes:

```php
// Default: fast but write not acknowledged
$client->events()->send($datasource, $events);

// With wait: slower but write is acknowledged (HTTP 200)
$client->events()->send($datasource, $events, wait: true);
```

Use `wait: true` when data loss avoidance is critical.

