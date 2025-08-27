<?php

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Streeboga\Genesis\GenesisClient;

it('adds Authorization header when using asUser and does not modify original client', function () {
    $mock = new MockHandler([new Response(200, [], json_encode(['ok' => true]))]);
    $handlerStack = HandlerStack::create($mock);
    $baseUrl = 'https://api.genesis.com/v1/';
    $httpClient = new Client(['handler' => $handlerStack, 'base_uri' => $baseUrl]);

    $config = \Streeboga\Genesis\Config::fromArray(['api_key' => 'service_key', 'base_url' => $baseUrl]);
    $serviceClient = GenesisClient::fromConfig($config, $httpClient);

    $userClient = $serviceClient->asUser('user_token_123');

    $userClient->get('projects/1/users/1/profile');

    $req = $mock->getLastRequest();
    expect($req->getHeaderLine('Authorization'))->toBe('Bearer user_token_123');
    expect($req->getHeaderLine('X-API-Key'))->toBe('service_key');

    // original client's request should not have been sent yet; simulate another response and call
    $mock->append(new Response(200, [], json_encode(['ok' => true])));
    $serviceClient->get('projects/1/users/1/profile');
    $req2 = $mock->getLastRequest();
    expect($req2->hasHeader('Authorization'))->toBeFalse();
});


