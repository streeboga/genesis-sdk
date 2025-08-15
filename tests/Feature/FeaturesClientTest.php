<?php

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Streeboga\Genesis\FeaturesClient;
use Streeboga\Genesis\GenesisClient;

it('can get features for a user', function () {
    $featuresResponse = ['features' => [['name' => 'api-calls']]];
    $mock = new MockHandler([
        new Response(200, [], json_encode($featuresResponse)),
    ]);
    $handlerStack = HandlerStack::create($mock);
    $baseUrl = 'https://api.genesis.com/v1/';
    $httpClient = new Client([
        'handler' => $handlerStack,
        'base_uri' => $baseUrl,
    ]);

    $config = \Streeboga\Genesis\Config::fromArray(['api_key' => 'test_api_key', 'base_url' => $baseUrl]);
    $genesisClient = GenesisClient::fromConfig($config, $httpClient);
    $features = $genesisClient->features->getFeatures(1, 1);

    expect($features)->toBe($featuresResponse);
    expect($mock->getLastRequest()->getMethod())->toBe('GET');
    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/v1/projects/1/users/1/features');
});

it('can check a feature for a user', function () {
    $checkResponse = ['has_access' => true];
    $mock = new MockHandler([
        new Response(200, [], json_encode($checkResponse)),
    ]);
    $handlerStack = HandlerStack::create($mock);
    $baseUrl = 'https://api.genesis.com/v1/';
    $httpClient = new Client([
        'handler' => $handlerStack,
        'base_uri' => $baseUrl,
    ]);

    $config = \Streeboga\Genesis\Config::fromArray(['api_key' => 'test_api_key', 'base_url' => $baseUrl]);
    $genesisClient = GenesisClient::fromConfig($config, $httpClient);
    $check = $genesisClient->features->checkFeature(1, 1, 'api-calls');

    expect($check)->toBe($checkResponse);
    expect($mock->getLastRequest()->getMethod())->toBe('GET');
    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/v1/projects/1/users/1/features/api-calls/check');
});

it('can consume a feature for a user', function () {
    $consumeResponse = ['success' => true];
    $mock = new MockHandler([
        new Response(200, [], json_encode($consumeResponse)),
    ]);
    $handlerStack = HandlerStack::create($mock);
    $baseUrl = 'https://api.genesis.com/v1/';
    $httpClient = new Client([
        'handler' => $handlerStack,
        'base_uri' => $baseUrl,
    ]);

    $config = \Streeboga\Genesis\Config::fromArray(['api_key' => 'test_api_key', 'base_url' => $baseUrl]);
    $genesisClient = GenesisClient::fromConfig($config, $httpClient);
    $consume = $genesisClient->features->consumeFeature(1, 1, 'api-calls', 5);

    expect($consume)->toBe($consumeResponse);
    expect($mock->getLastRequest()->getMethod())->toBe('POST');
    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/v1/projects/1/users/1/features/consume');
    expect($mock->getLastRequest()->getBody()->getContents())->toBe(json_encode(['feature' => 'api-calls', 'amount' => 5]));
});

it('can get stats for a user', function () {
    $statsResponse = ['consumptions' => []];
    $mock = new MockHandler([
        new Response(200, [], json_encode($statsResponse)),
    ]);
    $handlerStack = HandlerStack::create($mock);
    $baseUrl = 'https://api.genesis.com/v1/';
    $httpClient = new Client([
        'handler' => $handlerStack,
        'base_uri' => $baseUrl,
    ]);

    $config = \Streeboga\Genesis\Config::fromArray(['api_key' => 'test_api_key', 'base_url' => $baseUrl]);
    $genesisClient = GenesisClient::fromConfig($config, $httpClient);
    $stats = $genesisClient->features->getStats(1, 1);

    expect($stats)->toBe($statsResponse);
    expect($mock->getLastRequest()->getMethod())->toBe('GET');
    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/v1/projects/1/users/1/features/stats');
});
