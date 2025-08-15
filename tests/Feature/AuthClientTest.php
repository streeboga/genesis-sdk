<?php

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Streeboga\Genesis\GenesisClient;

it('can login and receive tokens', function () {
    $loginResponse = ['data' => ['tokens' => ['access_token' => 'abc', 'refresh_token' => 'r1']]];
    $mock = new MockHandler([
        new Response(200, [], json_encode($loginResponse)),
    ]);
    $handlerStack = HandlerStack::create($mock);
    $baseUrl = 'https://api.genesis.com/v1/';
    $httpClient = new Client(['handler' => $handlerStack, 'base_uri' => $baseUrl]);

    $config = \Streeboga\Genesis\Config::fromArray(['api_key' => 'test_api_key', 'base_url' => $baseUrl]);
    $genesis = GenesisClient::fromConfig($config, $httpClient);

    $authClient = $genesis->auth;
    $res = $authClient->login(['email' => 'a@b.com', 'password' => 'secret']);

    expect($res)->toBe($loginResponse);
    expect($mock->getLastRequest()->getMethod())->toBe('POST');
    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/v1/auth/login');
});

it('can register a new user', function () {
    $regResponse = ['data' => ['user_uuid' => 'u1']];
    $mock = new MockHandler([new Response(201, [], json_encode($regResponse))]);
    $handlerStack = HandlerStack::create($mock);
    $baseUrl = 'https://api.genesis.com/v1/';
    $httpClient = new Client(['handler' => $handlerStack, 'base_uri' => $baseUrl]);

    $config = \Streeboga\Genesis\Config::fromArray(['api_key' => 'test_api_key', 'base_url' => $baseUrl]);
    $genesis = GenesisClient::fromConfig($config, $httpClient);

    $res = $genesis->auth->register(['email' => 'new@user.com', 'name' => 'New', 'project_uuid' => 'p1']);

    expect($res)->toBe($regResponse);
    expect($mock->getLastRequest()->getMethod())->toBe('POST');
    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/v1/auth/register');
});

it('can refresh tokens', function () {
    $refreshResponse = ['data' => ['tokens' => ['access_token' => 'new']]];
    $mock = new MockHandler([new Response(200, [], json_encode($refreshResponse))]);
    $handlerStack = HandlerStack::create($mock);
    $baseUrl = 'https://api.genesis.com/v1/';
    $httpClient = new Client(['handler' => $handlerStack, 'base_uri' => $baseUrl]);

    $config = \Streeboga\Genesis\Config::fromArray(['api_key' => 'test_api_key', 'base_url' => $baseUrl]);
    $genesis = GenesisClient::fromConfig($config, $httpClient);

    $res = $genesis->auth->refresh('r1');

    expect($res)->toBe($refreshResponse);
    expect($mock->getLastRequest()->getMethod())->toBe('POST');
    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/v1/auth/refresh');
});

it('can logout', function () {
    $logoutResponse = ['success' => true];
    $mock = new MockHandler([new Response(200, [], json_encode($logoutResponse))]);
    $handlerStack = HandlerStack::create($mock);
    $baseUrl = 'https://api.genesis.com/v1/';
    $httpClient = new Client(['handler' => $handlerStack, 'base_uri' => $baseUrl]);

    $config = \Streeboga\Genesis\Config::fromArray(['api_key' => 'test_api_key', 'base_url' => $baseUrl]);
    $genesis = GenesisClient::fromConfig($config, $httpClient);

    $res = $genesis->auth->logout();

    expect($res)->toBe($logoutResponse);
    expect($mock->getLastRequest()->getMethod())->toBe('POST');
    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/v1/auth/logout');
});
