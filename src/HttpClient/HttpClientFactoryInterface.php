<?php

declare(strict_types=1);

namespace Brd6\TinybirdSdk\HttpClient;

use Brd6\TinybirdSdk\ClientOptions;
use Http\Client\Common\HttpMethodsClientInterface;

interface HttpClientFactoryInterface
{
    public function create(ClientOptions $options): HttpMethodsClientInterface;
}
