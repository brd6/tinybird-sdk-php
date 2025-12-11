<?php

declare(strict_types=1);

namespace Brd6\Test\TinybirdSdk;

use Brd6\TinybirdSdk\ClientOptions;
use Brd6\TinybirdSdk\Enum\Region;

class ClientOptionsTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $options = new ClientOptions();

        $this->assertSame('', $options->getToken());
        $this->assertSame(Region::GCP_EUROPE_WEST3->getBaseUrl(), $options->getBaseUrl());
        $this->assertSame('v0', $options->getApiVersion());
        $this->assertSame(60, $options->getTimeout());
        $this->assertFalse($options->hasCompression());
        $this->assertNull($options->getHttpClient());
        $this->assertFalse($options->hasToken());
    }

    public function testSetToken(): void
    {
        $options = (new ClientOptions())->setToken('test-token');

        $this->assertSame('test-token', $options->getToken());
        $this->assertTrue($options->hasToken());
    }

    public function testSetBaseUrl(): void
    {
        $options = (new ClientOptions())->setBaseUrl('https://custom.api.com/');

        $this->assertSame('https://custom.api.com', $options->getBaseUrl());
    }

    public function testSetRegion(): void
    {
        $options = (new ClientOptions())->setRegion(Region::AWS_US_EAST_1);

        $this->assertSame(Region::AWS_US_EAST_1->getBaseUrl(), $options->getBaseUrl());
    }

    public function testUseLocal(): void
    {
        $options = (new ClientOptions())->useLocal();

        $this->assertSame('http://localhost:7181', $options->getBaseUrl());
    }

    public function testUseLocalWithCustomPort(): void
    {
        $options = (new ClientOptions())->useLocal(8080);

        $this->assertSame('http://localhost:8080', $options->getBaseUrl());
    }

    public function testSetTimeout(): void
    {
        $options = (new ClientOptions())->setTimeout(120);

        $this->assertSame(120, $options->getTimeout());
    }

    public function testCompression(): void
    {
        $options = (new ClientOptions())->setCompression(true);

        $this->assertTrue($options->hasCompression());

        $options->setCompression(false);

        $this->assertFalse($options->hasCompression());
    }

    public function testSetApiVersion(): void
    {
        $options = (new ClientOptions())->setApiVersion('v1');

        $this->assertSame('v1', $options->getApiVersion());
    }

    public function testFluentInterface(): void
    {
        $options = (new ClientOptions())
            ->setToken('token')
            ->setRegion(Region::GCP_US_EAST4)
            ->setTimeout(30)
            ->setCompression(true)
            ->setApiVersion('v0');

        $this->assertSame('token', $options->getToken());
        $this->assertSame(Region::GCP_US_EAST4->getBaseUrl(), $options->getBaseUrl());
        $this->assertSame(30, $options->getTimeout());
        $this->assertTrue($options->hasCompression());
        $this->assertSame('v0', $options->getApiVersion());
    }
}
