# Quick Start - Tinybird SDK for PHP

This example demonstrates the basics of using the `tinybird-sdk-php` library. It's a simple command-line script that:

1. Connects to the Tinybird API
2. Lists existing Data Sources
3. Sends events to a Data Source
4. Queries the data using SQL

## Prerequisites

- PHP 8.1 or higher
- Composer
- Tinybird Account with an API Token
- A Data Source to send events to

## Setup

### 1. Install Dependencies

Navigate to this directory and install the required packages using Composer:

```bash
cd examples/quick-start
composer install
```

### 2. Environment Configuration

Create a `.env` file by copying the example file:

```bash
cp env.example .env
```

Open the `.env` file and add your Tinybird token and Data Source name:

```dotenv
TINYBIRD_TOKEN="p.your_token_here"
TINYBIRD_DATASOURCE="events"
```

### 3. Create a Data Source (if needed)

If you don't have a Data Source yet, you can create one in the Tinybird UI or use the CLI:

```bash
tb datasource create events --schema "timestamp DateTime, event String, user_id String, page String"
```

## Usage

Execute the script from your terminal:

```bash
php index.php
```

Or use the composer script:

```bash
composer start
```

The script will:
- Connect to your Tinybird workspace
- List existing Data Sources
- Send sample events to your Data Source
- Query and display event counts grouped by type

