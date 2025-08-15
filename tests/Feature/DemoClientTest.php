<?php

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Streeboga\Genesis\GenesisClient;

it('can get demo status for a user', function () {
    $demoStatusResponse = ['has_demo' => true];
    $mock = new MockHandler([
        new Response(200, [], json_encode($demoStatusResponse)),
    ]);
    $handlerStack = HandlerStack::create($mock);
    $baseUrl = 'https://api.genesis.com/v1/';
    $httpClient = new Client([
        'handler' => $handlerStack,
        'base_uri' => $baseUrl,
    ]);

    $config = \Streeboga\Genesis\Config::fromArray(['api_key' => 'test_api_key', 'base_url' => $baseUrl]);
    $genesisClient = GenesisClient::fromConfig($config, $httpClient);
    $status = $genesisClient->demo->getStatus(1, 1);

    expect($status)->toBe($demoStatusResponse);
    expect($mock->getLastRequest()->getMethod())->toBe('GET');
    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/v1/projects/1/users/1/demo/status');
});

it('can give a demo to a user', function () {
    $giveDemoResponse = ['success' => true];
    $mock = new MockHandler([
        new Response(200, [], json_encode($giveDemoResponse)),
    ]);
    $handlerStack = HandlerStack::create($mock);
    $baseUrl = 'https://api.genesis.com/v1/';
    $httpClient = new Client([
        'handler' => $handlerStack,
        'base_uri' => $baseUrl,
    ]);

    $config = \Streeboga\Genesis\Config::fromArray(['api_key' => 'test_api_key', 'base_url' => $baseUrl]);
    $genesisClient = GenesisClient::fromConfig($config, $httpClient);
    $response = $genesisClient->demo->giveDemo(1, 1, 'standard', 7);

    expect($response)->toBe($giveDemoResponse);
    expect($mock->getLastRequest()->getMethod())->toBe('POST');
    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/v1/projects/1/users/1/demo/give');
    expect($mock->getLastRequest()->getBody()->getContents())->toBe(json_encode(['type' => 'standard', 'days' => 7]));
});

it('can extend a demo for a user', function () {
    $extendDemoResponse = ['success' => true];
    $mock = new MockHandler([
        new Response(200, [], json_encode($extendDemoResponse)),
    ]);
    $handlerStack = HandlerStack::create($mock);
    $baseUrl = 'https://api.genesis.com/v1/';
    $httpClient = new Client([
        'handler' => $handlerStack,
        'base_uri' => $baseUrl,
    ]);

    $config = \Streeboga\Genesis\Config::fromArray(['api_key' => 'test_api_key', 'base_url' => $baseUrl]);
    $genesisClient = GenesisClient::fromConfig($config, $httpClient);
    $response = $genesisClient->demo->extendDemo(1, 1, 'api-calls', 7);

    expect($response)->toBe($extendDemoResponse);
    expect($mock->getLastRequest()->getMethod())->toBe('POST');
    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/v1/projects/1/users/1/demo/extend');
    expect($mock->getLastRequest()->getBody()->getContents())->toBe(json_encode(['feature' => 'api-calls', 'days' => 7]));
});

it('can revoke a demo for a user', function () {
    $revokeDemoResponse = ['success' => true];
    $mock = new MockHandler([
        new Response(200, [], json_encode($revokeDemoResponse)),
    ]);
    $handlerStack = HandlerStack::create($mock);
    $baseUrl = 'https://api.genesis.com/v1/';
    $httpClient = new Client([
        'handler' => $handlerStack,
        'base_uri' => $baseUrl,
    ]);

    $config = \Streeboga\Genesis\Config::fromArray(['api_key' => 'test_api_key', 'base_url' => $baseUrl]);
    $genesisClient = GenesisClient::fromConfig($config, $httpClient);
    $response = $genesisClient->demo->revokeDemo(1, 1, 'api-calls');

    expect($response)->toBe($revokeDemoResponse);
    expect($mock->getLastRequest()->getMethod())->toBe('DELETE');
    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/v1/projects/1/users/1/demo/revoke');
    expect($mock->getLastRequest()->getBody()->getContents())->toBe(json_encode(['feature' => 'api-calls']));
});
