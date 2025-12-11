<?php

declare(strict_types=1);

namespace Brd6\TinybirdSdk;

use Brd6\TinybirdSdk\Endpoint\AnalyzeEndpoint;
use Brd6\TinybirdSdk\Endpoint\DataSourcesEndpoint;
use Brd6\TinybirdSdk\Endpoint\EventsEndpoint;
use Brd6\TinybirdSdk\Endpoint\JobsEndpoint;
use Brd6\TinybirdSdk\Endpoint\PipesEndpoint;
use Brd6\TinybirdSdk\Endpoint\QueryEndpoint;
use Brd6\TinybirdSdk\Endpoint\SinkPipesEndpoint;
use Brd6\TinybirdSdk\Endpoint\TokensEndpoint;
use Brd6\TinybirdSdk\Endpoint\VariablesEndpoint;
use Brd6\TinybirdSdk\Enum\Region;
use Brd6\TinybirdSdk\HttpClient\HttpRequestHandler;

class Client
{
    private ClientOptions $options;
    private HttpRequestHandler $handler;
    private AnalyzeEndpoint $analyzeEndpoint;
    private EventsEndpoint $eventsEndpoint;
    private DataSourcesEndpoint $dataSourcesEndpoint;
    private JobsEndpoint $jobsEndpoint;
    private PipesEndpoint $pipesEndpoint;
    private QueryEndpoint $queryEndpoint;
    private SinkPipesEndpoint $sinkPipesEndpoint;
    private TokensEndpoint $tokensEndpoint;
    private VariablesEndpoint $variablesEndpoint;

    public function __construct(?ClientOptions $options = null)
    {
        $this->options = $options ?? new ClientOptions();
        $this->handler = new HttpRequestHandler($this->options);

        $this->analyzeEndpoint = new AnalyzeEndpoint($this->handler);
        $this->eventsEndpoint = new EventsEndpoint($this->handler);
        $this->dataSourcesEndpoint = new DataSourcesEndpoint($this->handler);
        $this->jobsEndpoint = new JobsEndpoint($this->handler);
        $this->pipesEndpoint = new PipesEndpoint($this->handler);
        $this->queryEndpoint = new QueryEndpoint($this->handler);
        $this->sinkPipesEndpoint = new SinkPipesEndpoint($this->handler);
        $this->tokensEndpoint = new TokensEndpoint($this->handler);
        $this->variablesEndpoint = new VariablesEndpoint($this->handler);
    }

    public static function create(string $token): self
    {
        return new self((new ClientOptions())->setToken($token));
    }

    public static function forRegion(string $token, Region $region): self
    {
        return new self(
            (new ClientOptions())
                ->setToken($token)
                ->setRegion($region),
        );
    }

    public static function local(string $token = '', int $port = ClientOptions::DEFAULT_LOCAL_PORT): self
    {
        return new self(
            (new ClientOptions())
                ->setToken($token)
                ->useLocal($port),
        );
    }

    public function analyze(): AnalyzeEndpoint
    {
        return $this->analyzeEndpoint;
    }

    public function events(): EventsEndpoint
    {
        return $this->eventsEndpoint;
    }

    public function dataSources(): DataSourcesEndpoint
    {
        return $this->dataSourcesEndpoint;
    }

    public function jobs(): JobsEndpoint
    {
        return $this->jobsEndpoint;
    }

    public function pipes(): PipesEndpoint
    {
        return $this->pipesEndpoint;
    }

    public function query(): QueryEndpoint
    {
        return $this->queryEndpoint;
    }

    public function sinkPipes(): SinkPipesEndpoint
    {
        return $this->sinkPipesEndpoint;
    }

    public function tokens(): TokensEndpoint
    {
        return $this->tokensEndpoint;
    }

    public function variables(): VariablesEndpoint
    {
        return $this->variablesEndpoint;
    }

    public function getOptions(): ClientOptions
    {
        return $this->options;
    }
}
