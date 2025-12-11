<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use Brd6\TinybirdSdk\Client;
use Brd6\TinybirdSdk\Enum\Region;
use Brd6\TinybirdSdk\Exception\ApiException;
use Brd6\TinybirdSdk\Exception\AuthenticationException;
use Brd6\TinybirdSdk\RequestParameters\CreateTokenParams;
use Brd6\TinybirdSdk\RequestParameters\UpdateTokenParams;
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
    echo "\n=== Tinybird SDK - Tokens API Examples ===\n\n";

    try {
        loadEnv();
        $client = createClient();

        // 1. List tokens
        echo "\n--- list() ---\n";
        $list = $client->tokens()->list();
        echo "Found " . count($list) . " token(s)\n";
        foreach ($list as $token) {
            $scopes = implode(', ', array_map(fn ($s) => $s->type, $token->scopes));
            echo "  - {$token->name} [$scopes]\n";
        }

        // 2. Create token
        echo "\n--- create() ---\n";
        $name = 'sdk_example_' . time();
        try {
            $token = $client->tokens()->create(new CreateTokenParams(
                name: $name,
                scopes: ['PIPES:CREATE'],
                description: 'SDK example token',
            ));
            echo "Created: {$token->name}\n";
            echo "Token: {$token->token}\n";

            // 3. Retrieve
            echo "\n--- retrieve() ---\n";
            $token = $client->tokens()->retrieve($name);
            echo "Name: {$token->name}\n";
            echo "isAdmin: " . ($token->isAdmin() ? 'yes' : 'no') . "\n";

            // 4. Update
            echo "\n--- update() ---\n";
            $token = $client->tokens()->update($name, new UpdateTokenParams(
                description: 'Updated at ' . date('H:i:s'),
            ));
            echo "Updated: {$token->description}\n";

            // 5. Refresh
            echo "\n--- refresh() ---\n";
            $token = $client->tokens()->refresh($name);
            echo "New token: {$token->token}\n";

            // 6. Delete
            echo "\n--- remove() ---\n";
            $client->tokens()->remove($name);
            echo "Deleted: $name\n";
        } catch (ApiException $e) {
            echo "Token operation failed: {$e->getMessage()}\n";
        }

        echo "\n=== Done! ===\n";
    } catch (AuthenticationException $e) {
        exit("[ERROR] Authentication: " . $e->getMessage() . "\n");
    } catch (ApiException $e) {
        exit("[ERROR] API: " . $e->getMessage() . "\n");
    }
}

main();
