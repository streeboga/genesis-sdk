<?php

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Streeboga\Genesis\GenesisClient;

it('can create a subscription for a project', function () {
    $subResponse = ['subscription_uuid' => 'sub-123', 'status' => 'active'];
    $mock = new MockHandler([
        new Response(201, [], json_encode($subResponse)),
    ]);
    $handlerStack = HandlerStack::create($mock);
    $baseUrl = 'https://api.genesis.com/v1/';
    $httpClient = new Client(['handler' => $handlerStack, 'base_uri' => $baseUrl]);

    $config = \Streeboga\Genesis\Config::fromArray(['api_key' => 'test_api_key', 'base_url' => $baseUrl]);
    $genesis = GenesisClient::fromConfig($config, $httpClient);

    $res = $genesis->billing->createSubscription(1, ['plan_uuid' => 'plan-1', 'user_id' => 1]);

    expect($res)->toBe($subResponse);
    expect($mock->getLastRequest()->getMethod())->toBe('POST');
    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/v1/projects/1/subscriptions');
});

it('can initiate a payment', function () {
    $payResponse = ['transaction_uuid' => 'tx-123', 'payment_url' => '/checkout/tx-123', 'message' => 'Processing'];
    $mock = new MockHandler([
        new Response(200, [], json_encode($payResponse)),
    ]);
    $handlerStack = HandlerStack::create($mock);
    $baseUrl = 'https://api.genesis.com/v1/';
    $httpClient = new Client(['handler' => $handlerStack, 'base_uri' => $baseUrl]);

    $config = \Streeboga\Genesis\Config::fromArray(['api_key' => 'test_api_key', 'base_url' => $baseUrl]);
    $genesis = GenesisClient::fromConfig($config, $httpClient);

    $res = $genesis->billing->initiatePayment(['plan_uuid' => 'plan-1', 'checkout_token' => 'tok']);

    expect($res)->toBe($payResponse);
    expect($mock->getLastRequest()->getMethod())->toBe('POST');
    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/v1/payments');
});

it('can get plan features', function () {
    $features = ['features' => [['name' => 'api-calls', 'limit' => 1000]]];
    $mock = new MockHandler([
        new Response(200, [], json_encode($features)),
    ]);
    $handlerStack = HandlerStack::create($mock);
    $baseUrl = 'https://api.genesis.com/v1/';
    $httpClient = new Client(['handler' => $handlerStack, 'base_uri' => $baseUrl]);

    $config = \Streeboga\Genesis\Config::fromArray(['api_key' => 'test_api_key', 'base_url' => $baseUrl]);
    $genesis = GenesisClient::fromConfig($config, $httpClient);

    $res = $genesis->billing->getPlanFeatures(1, 'plan-1');

    expect($res)->toBe($features);
    expect($mock->getLastRequest()->getMethod())->toBe('GET');
    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/v1/projects/1/plans/plan-1/features');
});

it('can get overage for a user', function () {
    $overage = ['overage' => ['api_calls' => 10]];
    $mock = new MockHandler([
        new Response(200, [], json_encode($overage)),
    ]);
    $handlerStack = HandlerStack::create($mock);
    $baseUrl = 'https://api.genesis.com/v1/';
    $httpClient = new Client(['handler' => $handlerStack, 'base_uri' => $baseUrl]);

    $config = \Streeboga\Genesis\Config::fromArray(['api_key' => 'test_api_key', 'base_url' => $baseUrl]);
    $genesis = GenesisClient::fromConfig($config, $httpClient);

    $res = $genesis->billing->getOverage(1, 1);

    expect($res)->toBe($overage);
    expect($mock->getLastRequest()->getMethod())->toBe('GET');
    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/v1/projects/1/users/1/overage');
});

it('can consume overage', function () {
    $consume = ['success' => true, 'remaining' => 9];
    $mock = new MockHandler([
        new Response(200, [], json_encode($consume)),
    ]);
    $handlerStack = HandlerStack::create($mock);
    $baseUrl = 'https://api.genesis.com/v1/';
    $httpClient = new Client(['handler' => $handlerStack, 'base_uri' => $baseUrl]);

    $config = \Streeboga\Genesis\Config::fromArray(['api_key' => 'test_api_key', 'base_url' => $baseUrl]);
    $genesis = GenesisClient::fromConfig($config, $httpClient);

    $res = $genesis->billing->consumeOverage(1, 1, 'api_calls', 1);

    expect($res)->toBe($consume);
    expect($mock->getLastRequest()->getMethod())->toBe('POST');
    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/v1/projects/1/users/1/overage/consume');
    expect($mock->getLastRequest()->getBody()->getContents())->toBe(json_encode(['feature' => 'api_calls', 'amount' => 1]));
});
