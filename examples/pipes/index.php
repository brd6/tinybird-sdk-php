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
    echo "\n=== Tinybird SDK - Pipes API Examples ===\n\n";

    try {
        loadEnv();
        $client = createClient();

        // 1. List Pipes
        echo "\n--- list() ---\n";
        $pipes = $client->pipes()->list();
        echo "Found " . count($pipes) . " Pipe(s)\n";
        foreach ($pipes as $pipe) {
            $endpoint = $pipe->isApiEndpoint() ? ' [ENDPOINT]' : '';
            echo "  - {$pipe->name}$endpoint\n";
        }

        // 2. Retrieve Pipe details
        if (count($pipes) > 0) {
            echo "\n--- retrieve() ---\n";
            $pipe = $client->pipes()->retrieve($pipes[0]->name);
            echo "Name: {$pipe->name}\n";
            echo "Nodes: " . count($pipe->nodes) . "\n";
            foreach ($pipe->nodes as $node) {
                echo "  - {$node->name}\n";
            }
        }

        // 3. Query an endpoint
        $endpointPipe = null;
        foreach ($pipes as $pipe) {
            if ($pipe->isApiEndpoint()) {
                $endpointPipe = $pipe->name;
                break;
            }
        }

        if ($endpointPipe) {
            echo "\n--- query() ---\n";
            $result = $client->pipes()->query($endpointPipe);
            echo "Endpoint: $endpointPipe\n";
            echo "Rows: {$result->rows}\n";
            echo "Elapsed: {$result->getElapsedMs()}ms\n";

            if (count($result->data) > 0) {
                echo "First row: " . json_encode($result->data[0]) . "\n";
            }
        } else {
            echo "\n[INFO] No published endpoints found.\n";
        }

        echo "\n=== Done! ===\n";
    } catch (AuthenticationException $e) {
        exit("[ERROR] Authentication: " . $e->getMessage() . "\n");
    } catch (ApiException $e) {
        exit("[ERROR] API: " . $e->getMessage() . "\n");
    }
}

main();
