<?php

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Streeboga\Genesis\GenesisClient;

it('charges a project for a service', function () {
    $resp = ['charged' => true, 'amount' => 1000, 'currency' => 'RUB'];
    $mock = new MockHandler([new Response(200, [], json_encode($resp))]);
    $handler = HandlerStack::create($mock);
    $http = new Client(['handler' => $handler, 'base_uri' => 'https://api.genesis.com/v1/']);

    $config = \Streeboga\Genesis\Config::fromArray(['api_key' => 'k']);
    $genesis = GenesisClient::fromConfig($config, $http);

    $res = $genesis->billing->chargeProject(1, [
        'amount' => 1000,
        'currency' => 'RUB',
        'description' => 'One-time setup fee'
    ]);

    expect($res)->toBe($resp);
    $req = $mock->getLastRequest();
    expect($req->getMethod())->toBe('POST');
    expect($req->getUri()->getPath())->toBe('/v1/projects/1/billing/charge');
});

it('consumes feature with project balance (payg)', function () {
    $resp = ['success' => true, 'remaining' => 90];
    $mock = new MockHandler([new Response(200, [], json_encode($resp))]);
    $handler = HandlerStack::create($mock);
    $http = new Client(['handler' => $handler, 'base_uri' => 'https://api.genesis.com/v1/']);

    $config = \Streeboga\Genesis\Config::fromArray(['api_key' => 'k']);
    $genesis = GenesisClient::fromConfig($config, $http);

    $res = $genesis->features->consumeWithBalance(1, 2, 'api-calls', 10);

    expect($res)->toBe($resp);
    $req = $mock->getLastRequest();
    expect($req->getMethod())->toBe('POST');
    expect($req->getUri()->getPath())->toBe('/v1/projects/1/users/2/features/consume-with-balance');
});






