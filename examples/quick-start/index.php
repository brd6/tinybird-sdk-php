<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use Brd6\TinybirdSdk\Client;
use Brd6\TinybirdSdk\Exception\ApiException;
use Brd6\TinybirdSdk\Exception\AuthenticationException;
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
        echo "Connecting to Tinybird Local (localhost:$port)...\n\n";
        return Client::local($_ENV['TINYBIRD_TOKEN'], $port);
    }
    echo "Connecting to Tinybird Cloud...\n\n";
    return Client::create($_ENV['TINYBIRD_TOKEN']);
}

function main(): void
{
    echo "Tinybird SDK PHP - Quick Start\n";
    echo "==============================\n\n";

    try {
        loadEnv();
        $client = createClient();
        $datasource = $_ENV['TINYBIRD_DATASOURCE'];

        // List Data Sources
        echo "Listing Data Sources...\n";
        $dataSources = $client->dataSources()->list();
        echo "Found " . count($dataSources) . " Data Source(s):\n";
        foreach ($dataSources as $ds) {
            echo "  - {$ds->name} ({$ds->getRowCount()} rows)\n";
        }
        echo "\n";

        // Send events
        echo "Sending events to '$datasource'...\n";
        $result = $client->events()->send($datasource, [
            ['timestamp' => date('Y-m-d H:i:s'), 'event' => 'page_view', 'user_id' => 'user_' . rand(1, 100)],
            ['timestamp' => date('Y-m-d H:i:s'), 'event' => 'click', 'user_id' => 'user_' . rand(1, 100)],
        ]);
        echo "Sent {$result->successfulRows} events.\n\n";

        // Query data
        echo "Querying data...\n";
        $result = $client->query()->sql(
            "SELECT event, count() as count FROM $datasource GROUP BY event ORDER BY count DESC LIMIT 5"
        );
        foreach ($result->data as $row) {
            echo "  - {$row['event']}: {$row['count']} events\n";
        }
        echo "\nQuery executed in {$result->getElapsedTime()}s\n";

        echo "\nDone!\n";
    } catch (AuthenticationException $e) {
        exit("[ERROR] Authentication: " . $e->getMessage() . "\n");
    } catch (ApiException $e) {
        exit("[ERROR] API: " . $e->getMessage() . "\n");
    }
}

main();
