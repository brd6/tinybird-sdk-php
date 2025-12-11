<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use Brd6\TinybirdSdk\Client;
use Brd6\TinybirdSdk\Enum\JobKind;
use Brd6\TinybirdSdk\Enum\JobStatus;
use Brd6\TinybirdSdk\Enum\Region;
use Brd6\TinybirdSdk\Exception\ApiException;
use Brd6\TinybirdSdk\Exception\AuthenticationException;
use Brd6\TinybirdSdk\RequestParameters\JobsListParams;
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
    echo "\n=== Tinybird SDK - Jobs API Examples ===\n\n";

    try {
        loadEnv();
        $client = createClient();

        // 1. List recent jobs
        echo "\n--- list() ---\n";
        $jobs = $client->jobs()->list();
        echo "Found " . count($jobs) . " job(s) (last 48h)\n";
        foreach (array_slice($jobs, 0, 5) as $job) {
            $status = $job->status?->value ?? 'unknown';
            $kind = $job->kind?->value ?? 'unknown';
            echo "  - [$status] $kind: {$job->id}\n";
        }

        // 2. Filter by status
        echo "\n--- list() with filters ---\n";
        $doneJobs = $client->jobs()->list(new JobsListParams(status: JobStatus::DONE));
        echo "Completed jobs: " . count($doneJobs) . "\n";

        $importJobs = $client->jobs()->list(new JobsListParams(kind: JobKind::IMPORT));
        echo "Import jobs: " . count($importJobs) . "\n";

        // 3. Get job details
        if (count($jobs) > 0) {
            echo "\n--- retrieve() ---\n";
            $job = $client->jobs()->retrieve($jobs[0]->id);
            echo "Job: {$job->id}\n";
            echo "Kind: " . ($job->kind?->value ?? 'unknown') . "\n";
            echo "Status: " . ($job->status?->value ?? 'unknown') . "\n";
            echo "Cancellable: " . ($job->isCancellable ? 'yes' : 'no') . "\n";
            if ($job->statistics) {
                echo "Rows: {$job->getRowCount()}\n";
            }
        }

        echo "\n=== Done! ===\n";
    } catch (AuthenticationException $e) {
        exit("[ERROR] Authentication: " . $e->getMessage() . "\n");
    } catch (ApiException $e) {
        exit("[ERROR] API: " . $e->getMessage() . "\n");
    }
}

main();
