<?php

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Streeboga\Genesis\GenesisClient;

it('sends forgot password request', function () {
    $resp = ['success' => true];
    $mock = new MockHandler([new Response(200, [], json_encode($resp))]);
    $handler = HandlerStack::create($mock);
    $http = new Client(['handler' => $handler, 'base_uri' => 'https://api.genesis.com/v1/']);

    $config = \Streeboga\Genesis\Config::fromArray(['api_key' => 'k']);
    $gen = GenesisClient::fromConfig($config, $http);

    $r = $gen->auth->forgotPassword(['email' => 'user@example.com']);
    expect($r)->toBe($resp);
    $req = $mock->getLastRequest();
    expect($req->getMethod())->toBe('POST');
    expect($req->getUri()->getPath())->toBe('/v1/auth/password/forgot');
});

it('resets password', function () {
    $resp = ['success' => true];
    $mock = new MockHandler([new Response(200, [], json_encode($resp))]);
    $handler = HandlerStack::create($mock);
    $http = new Client(['handler' => $handler, 'base_uri' => 'https://api.genesis.com/v1/']);

    $config = \Streeboga\Genesis\Config::fromArray(['api_key' => 'k']);
    $gen = GenesisClient::fromConfig($config, $http);

    $r = $gen->auth->resetPassword(['token' => 't', 'password' => 'new']);
    expect($r)->toBe($resp);
    $req = $mock->getLastRequest();
    expect($req->getUri()->getPath())->toBe('/v1/auth/password/reset');
});

it('resends verification email', function () {
    $resp = ['ok' => true];
    $mock = new MockHandler([new Response(200, [], json_encode($resp))]);
    $handler = HandlerStack::create($mock);
    $http = new Client(['handler' => $handler, 'base_uri' => 'https://api.genesis.com/v1/']);

    $config = \Streeboga\Genesis\Config::fromArray(['api_key' => 'k']);
    $gen = GenesisClient::fromConfig($config, $http);

    $r = $gen->auth->resendVerification(['email' => 'user@example.com']);
    expect($r)->toBe($resp);
    $req = $mock->getLastRequest();
    expect($req->getUri()->getPath())->toBe('/v1/auth/resend-verification');
});

it('verifies email', function () {
    $resp = ['verified' => true];
    $mock = new MockHandler([new Response(200, [], json_encode($resp))]);
    $handler = HandlerStack::create($mock);
    $http = new Client(['handler' => $handler, 'base_uri' => 'https://api.genesis.com/v1/']);

    $config = \Streeboga\Genesis\Config::fromArray(['api_key' => 'k']);
    $gen = GenesisClient::fromConfig($config, $http);

    $r = $gen->auth->verifyEmail(['token' => 'tok']);
    expect($r)->toBe($resp);
    $req = $mock->getLastRequest();
    expect($req->getUri()->getPath())->toBe('/v1/auth/email/verify');
});

it('sends 2fa challenge and verifies', function () {
    $mock = new MockHandler([
        new Response(200, [], json_encode(['challenge' => 'ok'])),
        new Response(200, [], json_encode(['verified' => true])),
    ]);
    $handler = HandlerStack::create($mock);
    $http = new Client(['handler' => $handler, 'base_uri' => 'https://api.genesis.com/v1/']);

    $config = \Streeboga\Genesis\Config::fromArray(['api_key' => 'k']);
    $gen = GenesisClient::fromConfig($config, $http);

    $c = $gen->auth->twoFaChallenge(['method' => 'sms']);
    expect($c['challenge'])->toBe('ok');

    $v = $gen->auth->twoFaVerify(['code' => '123456']);
    expect($v['verified'])->toBeTrue();

    $req1 = $mock->getLastRequest();
    // last request corresponds to verify; check second-to-last for challenge
    // we can only assert paths generically
    expect($req1->getUri()->getPath())->toBe('/v1/auth/2fa/verify');
});






