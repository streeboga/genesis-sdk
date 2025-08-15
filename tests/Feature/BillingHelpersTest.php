<?php

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Streeboga\Genesis\GenesisClient;

it('can get plan metadata', function () {
    $meta = ['plan_uuid' => 'plan-1', 'name' => 'Premium', 'price' => 1000, 'currency' => 'RUB'];
    $mock = new MockHandler([
        new Response(200, [], json_encode($meta)),
    ]);
    $handlerStack = HandlerStack::create($mock);
    $baseUrl = 'https://api.genesis.com/v1/';
    $httpClient = new Client(['handler' => $handlerStack, 'base_uri' => $baseUrl]);

    $config = \Streeboga\Genesis\Config::fromArray(['api_key' => 'test_api_key', 'base_url' => $baseUrl]);
    $genesis = GenesisClient::fromConfig($config, $httpClient);

    $res = $genesis->billing->getPlanMetadata(1, 'plan-1');

    expect($res)->toBe($meta);
    expect($mock->getLastRequest()->getMethod())->toBe('GET');
    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/v1/projects/1/plans/plan-1/metadata');
});

it('can calculate overage price', function () {
    $price = ['feature' => 'api_calls', 'amount' => 10, 'unit_price' => 0.05, 'total' => 0.5, 'currency' => 'USD'];
    $mock = new MockHandler([
        new Response(200, [], json_encode($price)),
    ]);
    $handlerStack = HandlerStack::create($mock);
    $baseUrl = 'https://api.genesis.com/v1/';
    $httpClient = new Client(['handler' => $handlerStack, 'base_uri' => $baseUrl]);

    $config = \Streeboga\Genesis\Config::fromArray(['api_key' => 'test_api_key', 'base_url' => $baseUrl]);
    $genesis = GenesisClient::fromConfig($config, $httpClient);

    $res = $genesis->billing->calculateOveragePrice(1, 'plan-1', 'api_calls', 10);

    expect($res)->toBe($price);
    expect($mock->getLastRequest()->getMethod())->toBe('POST');
    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/v1/projects/1/plans/plan-1/overage/calc');
    expect($mock->getLastRequest()->getBody()->getContents())->toBe(json_encode(['feature' => 'api_calls', 'amount' => 10]));
});
