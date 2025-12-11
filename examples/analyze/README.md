# Tinybird SDK PHP - Analyze API Example

This example demonstrates the Analyze API, which infers schema from CSV, NDJSON, or Parquet data.

## Setup

```bash
cd examples/analyze
cp env.example .env
# Edit .env with your token
composer install
```

## Run

```bash
php index.php
```

## What it does

The example demonstrates:

| Method | Description |
|--------|-------------|
| `analyzeContent()` | Analyze raw CSV/NDJSON/Parquet content |
| `analyzeRecords()` | Analyze PHP arrays as NDJSON |
| `analyzeUrl()` | Analyze a remote file by URL |

## Use cases

The Analyze API is useful for:

- **Schema inference**: Automatically detect column types from sample data
- **Data Source creation**: Generate schema for `.datasource` files
- **Data validation**: Check if data matches expected types before ingestion
- **Migration**: Analyze existing files to create Tinybird Data Sources

## Example output

```
Columns detected:
- date: Date
- product_id: String
- user_id: String
- event: String
- amount: Float32

Inferred Schema:
date Date, product_id String, user_id String, event String, amount Float32
```

## Related

- [Analyze API Documentation](https://www.tinybird.co/docs/api-reference/analyze-api)
- [Data Sources Documentation](https://www.tinybird.co/docs/forward/get-data-in/data-sources)

