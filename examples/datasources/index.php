<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use Brd6\TinybirdSdk\Client;
use Brd6\TinybirdSdk\Enum\Region;
use Brd6\TinybirdSdk\Exception\ApiException;
use Brd6\TinybirdSdk\Exception\AuthenticationException;
use Dotenv\Dotenv;

const TEST_DATASOURCE = 'sdk_test_events';

function loadEnv(): void
{
    if (!file_exists(__DIR__ . '/.env')) {
        exit("Error: .env file not found. Run: cp env.example .env\n");
    }
    Dotenv::createImmutable(__DIR__)->load();
    if (empty($_ENV['TINYBIRD_TOKEN'])) {
        exit("Error: TINYBIRD_TOKEN required.\n");
    }
}

function createClient(): Client
{
    $isLocal = in_array(strtolower($_ENV['TINYBIRD_LOCAL'] ?? ''), ['true', '1', 'yes'], true);
    if ($isLocal) {
        $port = (int) ($_ENV['TINYBIRD_PORT'] ?? 7181);
        echo "[INFO] Connecting to Tinybird Local (localhost:$port)\n";
        return Client::local($_ENV['TINYBIRD_TOKEN'], $port);
    }
    $region = Region::tryFrom($_ENV['TINYBIRD_REGION'] ?? '') ?? Region::GCP_EUROPE_WEST3;
    echo "[INFO] Connecting to Tinybird Cloud ($region->value)\n";
    return Client::forRegion($_ENV['TINYBIRD_TOKEN'], $region);
}

function main(): void
{
    echo "\n=== Tinybird SDK - Data Sources Examples ===\n\n";

    try {
        loadEnv();
        $client = createClient();

        // 1. List Data Sources
        echo "\n--- list() ---\n";
        $dataSources = $client->dataSources()->list();
        echo "Found " . count($dataSources) . " Data Source(s):\n";
        foreach (array_slice($dataSources, 0, 5) as $ds) {
            echo "  - {$ds->name} ({$ds->getRowCount()} rows)\n";
        }

        // 2. Retrieve Data Source details
        if (count($dataSources) > 0) {
            echo "\n--- retrieve() ---\n";
            $ds = $client->dataSources()->retrieve($dataSources[0]->name);
            echo "Name: {$ds->name}\n";
            echo "Type: {$ds->type}\n";
            echo "Rows: {$ds->getRowCount()}\n";
            echo "Columns:\n";
            foreach ($ds->columns as $col) {
                echo "  - {$col->name}: {$col->type}\n";
            }
        }

        // 3. Send events (if test datasource exists)
        $testDsExists = false;
        foreach ($dataSources as $ds) {
            if ($ds->name === TEST_DATASOURCE) {
                $testDsExists = true;
                break;
            }
        }

        if ($testDsExists) {
            echo "\n--- Events API (recommended for Forward) ---\n";
            $result = $client->events()->send(TEST_DATASOURCE, [
                ['timestamp' => date('Y-m-d H:i:s'), 'event' => 'test', 'user_id' => 'user_001', 'properties' => '{}'],
            ]);
            echo "Ingested: {$result->successfulRows} row(s)\n";
        } else {
            echo "\n[INFO] Deploy '" . TEST_DATASOURCE . "' with: cd tinybird && tb deploy\n";
        }

        echo "\n=== Done! ===\n";
    } catch (AuthenticationException $e) {
        exit("[ERROR] Authentication: " . $e->getMessage() . "\n");
    } catch (ApiException $e) {
        exit("[ERROR] API: " . $e->getMessage() . "\n");
    }
}

main();
