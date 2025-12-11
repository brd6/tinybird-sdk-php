<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use Brd6\TinybirdSdk\Client;
use Brd6\TinybirdSdk\Enum\Region;
use Brd6\TinybirdSdk\Enum\VariableType;
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
        exit("Error: TINYBIRD_TOKEN required (needs ADMIN scope).\n");
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
    echo "\n=== Tinybird SDK - Variables API Examples ===\n\n";

    try {
        loadEnv();
        $client = createClient();

        // 1. List variables
        echo "\n--- list() ---\n";
        $list = $client->variables()->list();
        echo "Found " . count($list) . " variable(s)\n";
        foreach ($list as $var) {
            echo "  - {$var->name} ({$var->type?->value})\n";
        }

        // 2. Create variable
        echo "\n--- create() ---\n";
        $name = 'sdk_example_' . time();
        try {
            $var = $client->variables()->create($name, 'secret_value_123', VariableType::SECRET);
            echo "Created: {$var->name} ({$var->type?->value})\n";

            // 3. Retrieve
            echo "\n--- retrieve() ---\n";
            $var = $client->variables()->retrieve($name);
            echo "Name: {$var->name}\n";
            echo "Type: {$var->type?->value}\n";

            // 4. Update
            echo "\n--- update() ---\n";
            $var = $client->variables()->update($name, 'updated_value_' . time());
            echo "Updated: {$var->name}\n";

            // 5. Delete
            echo "\n--- remove() ---\n";
            $client->variables()->remove($name);
            echo "Deleted: $name\n";
        } catch (ApiException $e) {
            echo "Variable operation failed: {$e->getMessage()}\n";
        }

        echo "\n=== Done! ===\n";
    } catch (AuthenticationException $e) {
        exit("[ERROR] Authentication: " . $e->getMessage() . "\n");
    } catch (ApiException $e) {
        exit("[ERROR] API: " . $e->getMessage() . "\n");
    }
}

main();
