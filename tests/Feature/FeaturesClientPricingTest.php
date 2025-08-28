<?php

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Streeboga\Genesis\GenesisClient;

it('retrieves features pricing for a user', function () {
    $pricing = ['features' => [
        ['name' => 'api-calls', 'price' => 0.01],
        ['name' => 'storage', 'price' => 0.10],
    ]];

    $mock = new MockHandler([new Response(200, [], json_encode($pricing))]);
    $handler = HandlerStack::create($mock);
    $baseUrl = 'https://api.genesis.com/v1/';
    $http = new Client(['handler' => $handler, 'base_uri' => $baseUrl]);

    $config = \Streeboga\Genesis\Config::fromArray(['api_key' => 'service_key', 'base_url' => $baseUrl]);
    $genesis = GenesisClient::fromConfig($config, $http);

    $res = $genesis->features->getPricing(1, 1);

    expect($res)->toBe($pricing);
    $req = $mock->getLastRequest();
    expect($req->getMethod())->toBe('GET');
    expect($req->getUri()->getPath())->toBe('/v1/projects/1/users/1/features/pricing');
});






