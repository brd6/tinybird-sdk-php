# Tinybird SDK PHP - Data Sources and Events Examples

This example demonstrates how to use the Tinybird SDK for PHP with Forward workspaces.

## What's Covered

| Method | Endpoint | Description |
|--------|----------|-------------|
| `dataSources()->list()` | `GET /v?/datasources` | List all Data Sources |
| `dataSources()->retrieve()` | `GET /v?/datasources/{name}` | Get a specific Data Source |
| `events()->send()` | `POST /v?/events` | Send events (recommended for ingestion) |

## Setup

### 1. Install dependencies

```bash
cd examples/datasources
composer install
```

### 2. Configure environment

```bash
cp env.example .env
```

Edit `.env` and add your Tinybird token:

```env
TINYBIRD_TOKEN=p.your_token_here
```

Get your token from [https://app.tinybird.co/tokens](https://app.tinybird.co/tokens) or use `tb token ls` for Tinybird Local.

### 3. Deploy the test Data Source

For Tinybird Local or Cloud, deploy the Data Source:

```bash
cd tinybird
tb deploy
```

This creates the `sdk_test_events` Data Source with NDJSON schema:

```
timestamp DateTime `json:$.timestamp`
event String `json:$.event`
user_id String `json:$.user_id`
properties String `json:$.properties`
```

## Run

```bash
php index.php
```

## Example Output

```
╔══════════════════════════════════════════════════════════════╗
║     Tinybird SDK PHP - Forward Workflow Examples             ║
╚══════════════════════════════════════════════════════════════╝
[INFO] Connecting to Tinybird Local (localhost:7181)

============================================================
  1. GET /v?/datasources - List Data Sources
============================================================

[INFO] Found 1 Data Source(s):
   - sdk_test_events (rows: 0, bytes: 0)
[OK] Listed Data Sources successfully.

============================================================
  2. GET /v?/datasources/{name} - Retrieve Data Source
============================================================

[INFO] Retrieving: sdk_test_events

   ID: t_28c2a193c3304ed9a3b0a86fdf2cdd11
   Name: sdk_test_events
   Type: ndjson
   Rows: 0
   Bytes: 0
   Created: 2025-12-11 08:38:34
   Columns:
     - timestamp: DateTime
     - event: String
     - user_id: String
     - properties: String
[OK] Retrieved Data Source successfully.

============================================================
  3. POST /v?/events - Send Events (recommended for Forward)
============================================================

[INFO] Sending events to: sdk_test_events

   Rows ingested: 3
   Quarantined: 0
[OK] Events sent successfully.
[INFO] Verifying ingestion...
   Data Source now has 3 rows
[OK] Events API works!

============================================================
  Examples Completed Successfully!
============================================================
```

## Forward Workflow

In Tinybird Forward, the recommended workflow is:

1. **Define schemas** in `.datasource` files
2. **Deploy** with `tb deploy`
3. **Ingest data** via Events API (`$client->events()->send()`)
4. **Query** via SQL or Pipe endpoints

## Resources

- [Tinybird Events API](https://www.tinybird.co/docs/api-reference/events-api)
- [Tinybird Data Sources API](https://www.tinybird.co/docs/api-reference/datasource-api)
- [Tinybird Forward Documentation](https://www.tinybird.co/docs/forward)
