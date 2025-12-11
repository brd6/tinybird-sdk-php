<?php

declare(strict_types=1);

namespace Brd6\TinybirdSdk\Enum;

enum Region: string
{
    // GCP Regions
    case GCP_EUROPE_WEST2 = 'gcp-europe-west2';
    case GCP_EUROPE_WEST3 = 'gcp-europe-west3';
    case GCP_US_EAST4 = 'gcp-us-east4';
    case GCP_NORTHAMERICA_NORTHEAST2 = 'gcp-northamerica-northeast2';

    // AWS Regions
    case AWS_EU_CENTRAL_1 = 'aws-eu-central-1';
    case AWS_EU_WEST_1 = 'aws-eu-west-1';
    case AWS_US_EAST_1 = 'aws-us-east-1';
    case AWS_US_WEST_2 = 'aws-us-west-2';

    // Local development
    case LOCAL = 'local';

    public function getBaseUrl(): string
    {
        return match ($this) {
            self::GCP_EUROPE_WEST2 => 'https://api.europe-west2.gcp.tinybird.co',
            self::GCP_EUROPE_WEST3 => 'https://api.tinybird.co',
            self::GCP_US_EAST4 => 'https://api.us-east.tinybird.co',
            self::GCP_NORTHAMERICA_NORTHEAST2 => 'https://api.northamerica-northeast2.gcp.tinybird.co',
            self::AWS_EU_CENTRAL_1 => 'https://api.eu-central-1.aws.tinybird.co',
            self::AWS_EU_WEST_1 => 'https://api.eu-west-1.aws.tinybird.co',
            self::AWS_US_EAST_1 => 'https://api.us-east.aws.tinybird.co',
            self::AWS_US_WEST_2 => 'https://api.us-west-2.aws.tinybird.co',
            self::LOCAL => 'http://localhost:7181',
        };
    }

    public static function fromTokenHost(string $host): ?self
    {
        return self::tryFrom($host);
    }
}
