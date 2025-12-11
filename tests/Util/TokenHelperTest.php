<?php

declare(strict_types=1);

namespace Brd6\Test\TinybirdSdk\Util;

use Brd6\Test\TinybirdSdk\TestCase;
use Brd6\TinybirdSdk\Enum\Region;
use Brd6\TinybirdSdk\Util\TokenHelper;

class TokenHelperTest extends TestCase
{
    private function createToken(string $host): string
    {
        $payload = base64_encode(json_encode(['u' => 'user_id', 'id' => 'token_id', 'host' => $host]));
        return "p.{$payload}.signature";
    }

    public function testExtractHostFromToken(): void
    {
        $token = $this->createToken('gcp-europe-west2');
        $this->assertSame('gcp-europe-west2', TokenHelper::extractHost($token));
    }

    public function testExtractHostFromAwsToken(): void
    {
        $token = $this->createToken('aws-us-east-1');
        $this->assertSame('aws-us-east-1', TokenHelper::extractHost($token));
    }

    public function testExtractHostFromLocalToken(): void
    {
        $token = $this->createToken('local');
        $this->assertSame('local', TokenHelper::extractHost($token));
    }

    public function testExtractHostReturnsNullForInvalidToken(): void
    {
        $this->assertNull(TokenHelper::extractHost('invalid-token'));
        $this->assertNull(TokenHelper::extractHost(''));
        $this->assertNull(TokenHelper::extractHost('p.invalid-base64.sig'));
    }

    public function testGetRegionFromToken(): void
    {
        $token = $this->createToken('gcp-europe-west2');
        $this->assertSame(Region::GCP_EUROPE_WEST2, TokenHelper::getRegion($token));
    }

    public function testGetRegionFromAwsToken(): void
    {
        $token = $this->createToken('aws-us-east-1');
        $this->assertSame(Region::AWS_US_EAST_1, TokenHelper::getRegion($token));
    }

    public function testGetRegionReturnsNullForInvalidToken(): void
    {
        $this->assertNull(TokenHelper::getRegion('invalid-token'));
    }

    public function testGetRegionReturnsNullForUnknownHost(): void
    {
        $token = $this->createToken('unknown-region');
        $this->assertNull(TokenHelper::getRegion($token));
    }

    public function testIsLocalTokenTrue(): void
    {
        $token = $this->createToken('local');
        $this->assertTrue(TokenHelper::isLocalToken($token));
    }

    public function testIsLocalTokenFalse(): void
    {
        $token = $this->createToken('gcp-europe-west2');
        $this->assertFalse(TokenHelper::isLocalToken($token));
    }

    public function testIsLocalTokenFalseForInvalid(): void
    {
        $this->assertFalse(TokenHelper::isLocalToken('invalid'));
    }
}
