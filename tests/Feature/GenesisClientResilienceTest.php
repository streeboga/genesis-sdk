<?php

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Streeboga\Genesis\GenesisClient;
use Streeboga\Genesis\Http\RetryPolicy;
use Streeboga\Genesis\Http\RateLimiter;
use Streeboga\Genesis\Http\CircuitBreaker;

it('retries on 500 then succeeds', function () {
    $mock = new MockHandler([
        new \GuzzleHttp\Exception\ServerException('err', new \GuzzleHttp\Psr7\Request('GET', 'test'), new Response(500, [], '{}')),
        new Response(200, [], json_encode(['ok' => true]))
    ]);
    $handler = HandlerStack::create($mock);
    $http = new Client(['handler' => $handler, 'base_uri' => 'https://api.genesis.com/v1/']);

    $config = \Streeboga\Genesis\Config::fromArray(['api_key' => 'k']);
    $client = GenesisClient::fromConfig($config, $http);
    $client->withRetryPolicy(new RetryPolicy(maxRetries: 1, baseDelayMs: 1, maxDelayMs: 2));

    $res = $client->get('projects/1/users/1/features');
    expect($res['ok'])->toBeTrue();
});

it('retries on 429 honoring Retry-After', function () {
    $mock = new MockHandler([
        new \GuzzleHttp\Exception\ClientException('too many', new \GuzzleHttp\Psr7\Request('GET', 'test'), new Response(429, ['Retry-After' => '0'])) ,
        new Response(200, [], json_encode(['ok' => true]))
    ]);
    $handler = HandlerStack::create($mock);
    $http = new Client(['handler' => $handler, 'base_uri' => 'https://api.genesis.com/v1/']);

    $config = \Streeboga\Genesis\Config::fromArray(['api_key' => 'k']);
    $client = GenesisClient::fromConfig($config, $http);
    $client->withRetryPolicy(new RetryPolicy(maxRetries: 1, baseDelayMs: 1, maxDelayMs: 2));

    $res = $client->get('projects/1/users/1/features');
    expect($res['ok'])->toBeTrue();
});

it('opens circuit breaker after failures and blocks calls', function () {
    $mock = new MockHandler([
        new \GuzzleHttp\Exception\ServerException('err', new \GuzzleHttp\Psr7\Request('GET', 'test'), new Response(500, [], '{}')),
        new \GuzzleHttp\Exception\ServerException('err', new \GuzzleHttp\Psr7\Request('GET', 'test'), new Response(500, [], '{}')),
    ]);
    $handler = HandlerStack::create($mock);
    $http = new Client(['handler' => $handler, 'base_uri' => 'https://api.genesis.com/v1/']);

    $config = \Streeboga\Genesis\Config::fromArray(['api_key' => 'k']);
    $client = GenesisClient::fromConfig($config, $http);
    $client->withRetryPolicy(new RetryPolicy(maxRetries: 0));
    $client->withCircuitBreaker(new CircuitBreaker(failureThreshold: 2, resetTimeoutSec: 60));

    // two failures to open
    try { $client->get('projects/1/users/1/features'); } catch (\Throwable $e) {}
    try { $client->get('projects/1/users/1/features'); } catch (\Throwable $e) {}

    // third call should be blocked immediately
    expect(fn() => $client->get('projects/1/users/1/features'))
        ->toThrow(\Streeboga\Genesis\Exceptions\ApiException::class);
});

it('applies simple rate limiter delay', function () {
    $mock = new MockHandler([
        new Response(200, [], json_encode(['ok' => true])),
        new Response(200, [], json_encode(['ok' => true])),
    ]);
    $handler = HandlerStack::create($mock);
    $http = new Client(['handler' => $handler, 'base_uri' => 'https://api.genesis.com/v1/']);

    $config = \Streeboga\Genesis\Config::fromArray(['api_key' => 'k']);
    $client = GenesisClient::fromConfig($config, $http);
    $client->withRateLimiter(new RateLimiter(minIntervalMs: 10));

    $t0 = microtime(true);
    $client->get('projects/1/users/1/features');
    $client->get('projects/1/users/1/features');
    $elapsedMs = (microtime(true) - $t0) * 1000;

    expect($elapsedMs)->toBeGreaterThanOrEqual(10);
});






