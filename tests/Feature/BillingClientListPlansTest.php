<?php

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Streeboga\Genesis\GenesisClient;

it('lists subscription plans for a project', function () {
    $plans = ['plans' => [
        ['uuid' => 'plan-1', 'name' => 'Basic', 'price' => 10],
        ['uuid' => 'plan-2', 'name' => 'Pro', 'price' => 30],
    ]];

    $mock = new MockHandler([new Response(200, [], json_encode($plans))]);
    $handler = HandlerStack::create($mock);
    $baseUrl = 'https://api.genesis.com/v1/';
    $http = new Client(['handler' => $handler, 'base_uri' => $baseUrl]);

    $config = \Streeboga\Genesis\Config::fromArray(['api_key' => 'service_key', 'base_url' => $baseUrl]);
    $genesis = GenesisClient::fromConfig($config, $http);

    $res = $genesis->billing->listPlans(1);

    expect($res)->toBe($plans);
    $req = $mock->getLastRequest();
    expect($req->getMethod())->toBe('GET');
    expect($req->getUri()->getPath())->toBe('/v1/projects/1/subscriptions/plans');
});

it('gets subscription status for user', function () {
    $status = ['status' => 'active', 'plan' => 'pro'];

    $mock = new MockHandler([new Response(200, [], json_encode($status))]);
    $handler = HandlerStack::create($mock);
    $baseUrl = 'https://api.genesis.com/v1/';
    $http = new Client(['handler' => $handler, 'base_uri' => $baseUrl]);

    $config = \Streeboga\Genesis\Config::fromArray(['api_key' => 'service_key', 'base_url' => $baseUrl]);
    $genesis = GenesisClient::fromConfig($config, $http);

    $res = $genesis->billing->getSubscriptionStatus(1, 42);

    expect($res)->toBe($status);
    $req = $mock->getLastRequest();
    expect($req->getMethod())->toBe('GET');
    expect($req->getUri()->getPath())->toBe('/v1/projects/1/users/42/subscription/status');
});


