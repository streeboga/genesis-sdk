<?php

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Streeboga\Genesis\GenesisClient;

it('gets widget info and config and assets', function () {
    $info = ['uuid' => 'w1', 'name' => 'Widget'];
    $config = ['theme' => 'dark'];
    $style = 'body{background:#000}';
    $script = 'console.log(1)';
    $health = ['status' => 'ok'];

    $mock = new MockHandler([
        new Response(200, [], json_encode($info)),
        new Response(200, [], json_encode($config)),
        new Response(200, [], $style),
        new Response(200, [], $script),
        new Response(200, [], json_encode($health)),
    ]);

    $handler = HandlerStack::create($mock);
    $http = new Client(['handler' => $handler, 'base_uri' => 'https://api.genesis.com/v1/']);

    $configObj = \Streeboga\Genesis\Config::fromArray(['api_key' => 'k']);
    $gen = GenesisClient::fromConfig($configObj, $http);

    expect($gen->embed->getInfo('w1'))->toBe($info);
    expect($gen->embed->getConfig('w1'))->toBe($config);
    expect($gen->embed->getStyle('w1'))->toBe($style);
    expect($gen->embed->getScript('w1'))->toBe($script);
    expect($gen->embed->health('w1'))->toBe($health);
});

it('auth login via widget and user profile and sessions', function () {
    $loginResp = ['user' => ['id' => 1], 'tokens' => ['access_token' => 'a']];
    $profile = ['id' => 1, 'name' => 'User'];
    $sessions = ['sessions' => [['id' => 's1']]];
    $revoke = ['success' => true];

    $mock = new MockHandler([
        new Response(200, [], json_encode($loginResp)),
        new Response(200, [], json_encode($profile)),
        new Response(200, [], json_encode($sessions)),
        new Response(200, [], json_encode($revoke)),
    ]);

    $handler = HandlerStack::create($mock);
    $http = new Client(['handler' => $handler, 'base_uri' => 'https://api.genesis.com/v1/']);

    $configObj = \Streeboga\Genesis\Config::fromArray(['api_key' => 'k']);
    $gen = GenesisClient::fromConfig($configObj, $http);

    $res = $gen->embed->authLogin('w1', ['email' => 'x', 'password' => 'p']);
    expect($res)->toBe($loginResp);

    $p = $gen->embed->usersProfile('w1');
    expect($p)->toBe($profile);

    $s = $gen->embed->sessionsList('w1', []);
    expect($s)->toBe($sessions);

    $rev = $gen->embed->revokeSession('w1', 's1');
    expect($rev)->toBe($revoke);
});


