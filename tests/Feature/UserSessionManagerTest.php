<?php

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Streeboga\Genesis\GenesisClient;
use Streeboga\Genesis\Session\UserSessionManager;

it('refreshes token when expired and returns client with new access token', function () {
    $baseUrl = 'https://api.genesis.com/v1/';

    // Prepare mock responses for refresh call
    $mock = new MockHandler([
        new Response(200, [], json_encode(['access_token' => 'new_access', 'refresh_token' => 'new_refresh', 'expires_in' => 60])),
    ]);
    $handlerStack = HandlerStack::create($mock);
    $httpClient = new Client(['handler' => $handlerStack, 'base_uri' => $baseUrl]);

    $config = \Streeboga\Genesis\Config::fromArray(['api_key' => 'service_key', 'base_url' => $baseUrl]);
    $serviceClient = GenesisClient::fromConfig($config, $httpClient);

    // create manager with controllable time
    $now = time();
    $fakeTime = function () use (&$now) { return $now; };
    $manager = new UserSessionManager($serviceClient, $fakeTime, 1);

    // set tokens so that they are about to expire
    $manager->setAccessTokenForTesting('old_access', 'old_refresh', 1); // expires in 1s

    // advance time beyond expiration
    $now += 5;

    $client = $manager->getClient();

    // after refresh, client should have new token
    // we call a method which will use the http client's last request
    // prepare an endpoint mock response
    $mock->append(new Response(200, [], json_encode(['profile' => true])));

    $client->get('projects/1/users/1/profile');
    $req = $mock->getLastRequest();
    expect($req->getHeaderLine('Authorization'))->toBe('Bearer new_access');
});

it('throws when no tokens present', function () {
    $baseUrl = 'https://api.genesis.com/v1/';
    $httpClient = new Client(['base_uri' => $baseUrl]);
    $config = \Streeboga\Genesis\Config::fromArray(['api_key' => 'service_key', 'base_url' => $baseUrl]);
    $serviceClient = GenesisClient::fromConfig($config, $httpClient);

    $manager = new UserSessionManager($serviceClient, fn() => time(), 1);

    expect(fn() => $manager->ensureValidAccessToken())->toThrow(\RuntimeException::class);
});
