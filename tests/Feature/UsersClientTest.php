<?php

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Streeboga\Genesis\GenesisClient;

it('can get user profile', function () {
    $profile = ['id' => 1, 'name' => 'User', 'email' => 'user@example.com'];
    $mock = new MockHandler([new Response(200, [], json_encode($profile))]);
    $handlerStack = HandlerStack::create($mock);
    $baseUrl = 'https://api.genesis.com/v1/';
    $httpClient = new Client(['handler' => $handlerStack, 'base_uri' => $baseUrl]);

    $config = \Streeboga\Genesis\Config::fromArray(['api_key' => 'test_api_key', 'base_url' => $baseUrl]);
    $genesis = GenesisClient::fromConfig($config, $httpClient);

    $res = $genesis->users->getProfile(1, 1);

    expect($res)->toBe($profile);
    expect($mock->getLastRequest()->getMethod())->toBe('GET');
    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/v1/projects/1/users/1/profile');
});

it('can update user profile', function () {
    $updated = ['id' => 1, 'name' => 'New Name', 'email' => 'user@example.com'];
    $mock = new MockHandler([new Response(200, [], json_encode($updated))]);
    $handlerStack = HandlerStack::create($mock);
    $baseUrl = 'https://api.genesis.com/v1/';
    $httpClient = new Client(['handler' => $handlerStack, 'base_uri' => $baseUrl]);

    $config = \Streeboga\Genesis\Config::fromArray(['api_key' => 'test_api_key', 'base_url' => $baseUrl]);
    $genesis = GenesisClient::fromConfig($config, $httpClient);

    $res = $genesis->users->updateProfile(1, 1, ['name' => 'New Name']);

    expect($res)->toBe($updated);
    expect($mock->getLastRequest()->getMethod())->toBe('PUT');
    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/v1/projects/1/users/1/profile');
    expect($mock->getLastRequest()->getBody()->getContents())->toBe(json_encode(['name' => 'New Name']));
});

it('can delete account', function () {
    $resp = ['success' => true];
    $mock = new MockHandler([new Response(200, [], json_encode($resp))]);
    $handlerStack = HandlerStack::create($mock);
    $baseUrl = 'https://api.genesis.com/v1/';
    $httpClient = new Client(['handler' => $handlerStack, 'base_uri' => $baseUrl]);

    $config = \Streeboga\Genesis\Config::fromArray(['api_key' => 'test_api_key', 'base_url' => $baseUrl]);
    $genesis = GenesisClient::fromConfig($config, $httpClient);

    $res = $genesis->users->deleteAccount(1, 1);

    expect($res)->toBe($resp);
    expect($mock->getLastRequest()->getMethod())->toBe('DELETE');
    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/v1/projects/1/users/1/account');
});

it('can list sessions', function () {
    $sessions = ['sessions' => [['id' => 's1', 'device' => 'iPhone']]];
    $mock = new MockHandler([new Response(200, [], json_encode($sessions))]);
    $handlerStack = HandlerStack::create($mock);
    $baseUrl = 'https://api.genesis.com/v1/';
    $httpClient = new Client(['handler' => $handlerStack, 'base_uri' => $baseUrl]);

    $config = \Streeboga\Genesis\Config::fromArray(['api_key' => 'test_api_key', 'base_url' => $baseUrl]);
    $genesis = GenesisClient::fromConfig($config, $httpClient);

    $res = $genesis->users->listSessions(1, 1);

    expect($res)->toBe($sessions);
    expect($mock->getLastRequest()->getMethod())->toBe('POST');
    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/v1/projects/1/users/1/sessions');
});

it('can revoke session', function () {
    $resp = ['success' => true];
    $mock = new MockHandler([new Response(200, [], json_encode($resp))]);
    $handlerStack = HandlerStack::create($mock);
    $baseUrl = 'https://api.genesis.com/v1/';
    $httpClient = new Client(['handler' => $handlerStack, 'base_uri' => $baseUrl]);

    $config = \Streeboga\Genesis\Config::fromArray(['api_key' => 'test_api_key', 'base_url' => $baseUrl]);
    $genesis = GenesisClient::fromConfig($config, $httpClient);

    $res = $genesis->users->revokeSession(1, 1, 's1');

    expect($res)->toBe($resp);
    expect($mock->getLastRequest()->getMethod())->toBe('DELETE');
    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/v1/projects/1/users/1/sessions/s1');
});






