<?php

declare(strict_types=1);

namespace Brd6\Test\TinybirdSdk\Enum;

use Brd6\Test\TinybirdSdk\TestCase;
use Brd6\TinybirdSdk\Enum\Region;

class RegionTest extends TestCase
{
    public function testGcpEuropeWest3IsDefault(): void
    {
        $this->assertSame('https://api.tinybird.co', Region::GCP_EUROPE_WEST3->getBaseUrl());
    }

    public function testGcpRegionsHaveCorrectUrls(): void
    {
        $this->assertSame('https://api.europe-west2.gcp.tinybird.co', Region::GCP_EUROPE_WEST2->getBaseUrl());
        $this->assertSame('https://api.us-east.tinybird.co', Region::GCP_US_EAST4->getBaseUrl());

        $expected = 'https://api.northamerica-northeast2.gcp.tinybird.co';
        $this->assertSame($expected, Region::GCP_NORTHAMERICA_NORTHEAST2->getBaseUrl());
    }

    public function testAwsRegionsHaveCorrectUrls(): void
    {
        $this->assertSame('https://api.eu-central-1.aws.tinybird.co', Region::AWS_EU_CENTRAL_1->getBaseUrl());
        $this->assertSame('https://api.eu-west-1.aws.tinybird.co', Region::AWS_EU_WEST_1->getBaseUrl());
        $this->assertSame('https://api.us-east.aws.tinybird.co', Region::AWS_US_EAST_1->getBaseUrl());
        $this->assertSame('https://api.us-west-2.aws.tinybird.co', Region::AWS_US_WEST_2->getBaseUrl());
    }

    public function testLocalRegion(): void
    {
        $this->assertSame('http://localhost:7181', Region::LOCAL->getBaseUrl());
        $this->assertSame('local', Region::LOCAL->value);
    }

    public function testFromTokenHostReturnsCorrectRegion(): void
    {
        $this->assertSame(Region::GCP_EUROPE_WEST2, Region::fromTokenHost('gcp-europe-west2'));
        $this->assertSame(Region::AWS_US_EAST_1, Region::fromTokenHost('aws-us-east-1'));
        $this->assertSame(Region::LOCAL, Region::fromTokenHost('local'));
    }

    public function testFromTokenHostReturnsNullForUnknown(): void
    {
        $this->assertNull(Region::fromTokenHost('unknown-region'));
        $this->assertNull(Region::fromTokenHost(''));
    }

    public function testAllRegionsHaveHttpsExceptLocal(): void
    {
        foreach (Region::cases() as $region) {
            if ($region === Region::LOCAL) {
                $this->assertStringStartsWith('http://', $region->getBaseUrl());
            } else {
                $this->assertStringStartsWith('https://', $region->getBaseUrl());
            }
        }
    }

    public function testEnumValuesMatchTokenHostFormat(): void
    {
        $this->assertSame('gcp-europe-west2', Region::GCP_EUROPE_WEST2->value);
        $this->assertSame('gcp-europe-west3', Region::GCP_EUROPE_WEST3->value);
        $this->assertSame('aws-us-east-1', Region::AWS_US_EAST_1->value);
    }
}
