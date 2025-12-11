<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use Brd6\TinybirdSdk\Client;
use Brd6\TinybirdSdk\Enum\Region;
use Brd6\TinybirdSdk\Exception\ApiException;
use Brd6\TinybirdSdk\Exception\AuthenticationException;
use Dotenv\Dotenv;

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
    echo "\n=== Tinybird SDK - Analyze API Examples ===\n\n";

    try {
        loadEnv();
        $client = createClient();

        // 1. Analyze CSV content
        echo "\n--- analyzeContent() - CSV ---\n";
        $csv = "date,product_id,user_id,event,amount\n2024-01-15,prod_123,user_456,purchase,99.99\n2024-01-16,prod_789,user_123,view,0";
        $result = $client->analyze()->analyzeContent($csv);
        echo "Schema: {$result->schema}\n";
        echo "Columns: " . count($result->columns) . "\n";

        // 2. Analyze NDJSON records
        echo "\n--- analyzeRecords() - NDJSON ---\n";
        $records = [
            ['timestamp' => '2024-01-15T10:30:00Z', 'user' => ['id' => 'user_123'], 'action' => 'login'],
            ['timestamp' => '2024-01-15T10:35:00Z', 'user' => ['id' => 'user_456'], 'action' => 'purchase', 'amount' => 129.99],
        ];
        $result = $client->analyze()->analyzeRecords($records);
        foreach ($result->columns as $col) {
            echo "  - {$col->name}: {$col->recommendedType}\n";
        }

        // 3. Analyze remote URL
        echo "\n--- analyzeUrl() - Remote file ---\n";
        try {
            $result = $client->analyze()->analyzeUrl('https://raw.githubusercontent.com/datasets/airport-codes/main/data/airport-codes.csv');
            echo "Columns detected: " . count($result->columns) . "\n";
        } catch (ApiException $e) {
            echo "URL analysis: {$e->getMessage()}\n";
        }

        echo "\n=== Done! ===\n";
    } catch (AuthenticationException $e) {
        exit("[ERROR] Authentication: " . $e->getMessage() . "\n");
    } catch (ApiException $e) {
        exit("[ERROR] API: " . $e->getMessage() . "\n");
    }
}

main();
