<?php

declare(strict_types=1);

namespace Brd6\Test\TinybirdSdk;

use Brd6\TinybirdSdk\Client;
use Brd6\TinybirdSdk\ClientOptions;
use Brd6\TinybirdSdk\Endpoint\DataSourcesEndpoint;
use Brd6\TinybirdSdk\Endpoint\EventsEndpoint;
use Brd6\TinybirdSdk\Endpoint\PipesEndpoint;
use Brd6\TinybirdSdk\Endpoint\QueryEndpoint;
use Brd6\TinybirdSdk\Enum\Region;

class ClientTest extends TestCase
{
    public function testCanBeInstantiated(): void
    {
        $client = new Client();

        $this->assertInstanceOf(Client::class, $client);
    }

    public function testCanBeInstantiatedWithOptions(): void
    {
        $options = (new ClientOptions())
            ->setToken('test-token')
            ->setRegion(Region::AWS_US_EAST_1);

        $client = new Client($options);

        $this->assertInstanceOf(Client::class, $client);
        $this->assertSame($options, $client->getOptions());
    }

    public function testCreateWithToken(): void
    {
        $client = Client::create('my-token');

        $this->assertInstanceOf(Client::class, $client);
        $this->assertSame('my-token', $client->getOptions()->getToken());
        $this->assertSame(Region::GCP_EUROPE_WEST3->getBaseUrl(), $client->getOptions()->getBaseUrl());
    }

    public function testForRegion(): void
    {
        $client = Client::forRegion('my-token', Region::AWS_US_WEST_2);

        $this->assertInstanceOf(Client::class, $client);
        $this->assertSame('my-token', $client->getOptions()->getToken());
        $this->assertSame(Region::AWS_US_WEST_2->getBaseUrl(), $client->getOptions()->getBaseUrl());
    }

    public function testLocal(): void
    {
        $client = Client::local('local-token');

        $this->assertInstanceOf(Client::class, $client);
        $this->assertSame('local-token', $client->getOptions()->getToken());
        $this->assertSame('http://localhost:7181', $client->getOptions()->getBaseUrl());
    }

    public function testLocalWithCustomPort(): void
    {
        $client = Client::local('', 8080);

        $this->assertSame('http://localhost:8080', $client->getOptions()->getBaseUrl());
    }

    public function testEventsReturnsEndpoint(): void
    {
        $client = new Client();

        $this->assertInstanceOf(EventsEndpoint::class, $client->events());
    }

    public function testDataSourcesReturnsEndpoint(): void
    {
        $client = new Client();

        $this->assertInstanceOf(DataSourcesEndpoint::class, $client->dataSources());
    }

    public function testPipesReturnsEndpoint(): void
    {
        $client = new Client();

        $this->assertInstanceOf(PipesEndpoint::class, $client->pipes());
    }

    public function testQueryReturnsEndpoint(): void
    {
        $client = new Client();

        $this->assertInstanceOf(QueryEndpoint::class, $client->query());
    }
}
