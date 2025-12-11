<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use Brd6\TinybirdSdk\Client;
use Brd6\TinybirdSdk\Enum\Region;
use Brd6\TinybirdSdk\Exception\ApiException;
use Brd6\TinybirdSdk\Exception\AuthenticationException;
use Brd6\TinybirdSdk\Util\NdjsonHelper;
use Dotenv\Dotenv;

function loadEnv(): void
{
    if (!file_exists(__DIR__ . '/.env')) {
        exit("Error: .env file not found. Run: cp env.example .env\n");
    }
    Dotenv::createImmutable(__DIR__)->load();
    if (empty($_ENV['TINYBIRD_TOKEN']) || empty($_ENV['TINYBIRD_DATASOURCE'])) {
        exit("Error: TINYBIRD_TOKEN and TINYBIRD_DATASOURCE required.\n");
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
    echo "\n=== Tinybird SDK - Events API Examples ===\n\n";

    try {
        loadEnv();
        $client = createClient();
        $datasource = $_ENV['TINYBIRD_DATASOURCE'];
        echo "[INFO] Target: $datasource\n";

        // 1. Send batch events (most common)
        echo "\n--- send() - Batch events ---\n";
        $events = [
            ['timestamp' => date('Y-m-d H:i:s'), 'event' => 'page_view', 'user_id' => 'user_' . rand(100, 999), 'properties' => '{}'],
            ['timestamp' => date('Y-m-d H:i:s'), 'event' => 'click', 'user_id' => 'user_' . rand(100, 999), 'properties' => '{}'],
        ];
        $result = $client->events()->send($datasource, $events);
        echo "Sent: {$result->successfulRows} rows\n";

        // 2. Send single JSON event
        echo "\n--- sendJson() - Single event ---\n";
        $result = $client->events()->sendJson($datasource, [
            'timestamp' => date('Y-m-d H:i:s'),
            'event' => 'purchase',
            'user_id' => 'user_' . rand(100, 999),
            'properties' => json_encode(['amount' => 49.99]),
        ]);
        echo "Sent: {$result->successfulRows} row\n";

        // 3. Send with wait=true (acknowledged)
        echo "\n--- send() with wait=true ---\n";
        $result = $client->events()->send($datasource, [
            ['timestamp' => date('Y-m-d H:i:s'), 'event' => 'critical', 'user_id' => 'user_001', 'properties' => '{}'],
        ], wait: true);
        echo "Acknowledged: {$result->successfulRows} row\n";

        // 4. Send gzip compressed
        echo "\n--- sendGzip() - Compressed ---\n";
        $events = [];
        for ($i = 0; $i < 50; $i++) {
            $events[] = ['timestamp' => date('Y-m-d H:i:s'), 'event' => "batch_$i", 'user_id' => 'user_001', 'properties' => '{}'];
        }
        $ndjson = NdjsonHelper::encode($events);
        $compressed = gzencode($ndjson, 9);
        if ($compressed !== false) {
            $result = $client->events()->sendGzip($datasource, $compressed);
            echo "Sent compressed: {$result->successfulRows} rows\n";
        }

        // Verify
        echo "\n--- Verify ingested data ---\n";
        sleep(1);
        $result = $client->query()->sql("SELECT event, count() as c FROM $datasource GROUP BY event ORDER BY c DESC LIMIT 5");
        foreach ($result->data as $row) {
            echo "  {$row['event']}: {$row['c']}\n";
        }

        echo "\n=== Done! ===\n";
    } catch (AuthenticationException $e) {
        exit("[ERROR] Authentication: " . $e->getMessage() . "\n");
    } catch (ApiException $e) {
        exit("[ERROR] API: " . $e->getMessage() . "\n");
    }
}

main();
