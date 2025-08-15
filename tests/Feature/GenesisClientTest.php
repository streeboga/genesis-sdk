<?php

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Streeboga\Genesis\GenesisClient;
use Streeboga\Genesis\Exceptions\NotFoundException;
use Streeboga\Genesis\Exceptions\ServerException;
use Streeboga\Genesis\Exceptions\ValidationException;

it('can be instantiated', function () {
    $client = new GenesisClient('test_api_key');
    expect($client)->toBeInstanceOf(GenesisClient::class);
});

it('holds the api key', function () {
    $client = new GenesisClient('test_api_key');
    expect($client->getApiKey())->toBe('test_api_key');
});

it('can make a get request', function () {
    $mock = new MockHandler([
        new Response(200, ['Content-Type' => 'application/json'], json_encode(['foo' => 'bar'])),
    ]);
    $handlerStack = HandlerStack::create($mock);
    $baseUrl = 'https://api.genesis.com/v1/';
    $httpClient = new Client([
        'handler' => $handlerStack,
        'base_uri' => $baseUrl,
    ]);

    $client = new GenesisClient('test_api_key', $baseUrl, $httpClient);
    $response = $client->get('test-endpoint');

    expect($response)->toBe(['foo' => 'bar']);
    expect($mock->getLastRequest()->getMethod())->toBe('GET');
    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/v1/test-endpoint');
    expect($mock->getLastRequest()->getHeaderLine('X-API-Key'))->toBe('test_api_key');
});

it('can make a post request', function () {
    $mock = new MockHandler([
        new Response(201, ['Content-Type' => 'application/json'], json_encode(['id' => 1, 'name' => 'test'])),
    ]);
    $handlerStack = HandlerStack::create($mock);
    $baseUrl = 'https://api.genesis.com/v1/';
    $httpClient = new Client([
        'handler' => $handlerStack,
        'base_uri' => $baseUrl,
    ]);

    $client = new GenesisClient('test_api_key', $baseUrl, $httpClient);
    $response = $client->post('test-endpoint', ['name' => 'test']);

    expect($response)->toBe(['id' => 1, 'name' => 'test']);
    expect($mock->getLastRequest()->getMethod())->toBe('POST');
    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/v1/test-endpoint');
    expect($mock->getLastRequest()->getHeaderLine('X-API-Key'))->toBe('test_api_key');
    expect($mock->getLastRequest()->getBody()->getContents())->toBe(json_encode(['name' => 'test']));
});

it('can make a put request', function () {
    $mock = new MockHandler([
        new Response(200, ['Content-Type' => 'application/json'], json_encode(['id' => 1, 'name' => 'updated'])),
    ]);
    $handlerStack = HandlerStack::create($mock);
    $baseUrl = 'https://api.genesis.com/v1/';
    $httpClient = new Client([
        'handler' => $handlerStack,
        'base_uri' => $baseUrl,
    ]);

    $client = new GenesisClient('test_api_key', $baseUrl, $httpClient);
    $response = $client->put('test-endpoint', ['name' => 'updated']);

    expect($response)->toBe(['id' => 1, 'name' => 'updated']);
    expect($mock->getLastRequest()->getMethod())->toBe('PUT');
    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/v1/test-endpoint');
    expect($mock->getLastRequest()->getHeaderLine('X-API-Key'))->toBe('test_api_key');
    expect($mock->getLastRequest()->getBody()->getContents())->toBe(json_encode(['name' => 'updated']));
});

it('can make a delete request', function () {
    $mock = new MockHandler([
        new Response(204),
    ]);
    $handlerStack = HandlerStack::create($mock);
    $baseUrl = 'https://api.genesis.com/v1/';
    $httpClient = new Client([
        'handler' => $handlerStack,
        'base_uri' => $baseUrl,
    ]);

    $client = new GenesisClient('test_api_key', $baseUrl, $httpClient);
    $response = $client->delete('test-endpoint');

    expect($response)->toBe([]);
    expect($mock->getLastRequest()->getMethod())->toBe('DELETE');
    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/v1/test-endpoint');
    expect($mock->getLastRequest()->getHeaderLine('X-API-Key'))->toBe('test_api_key');
});

it('throws not found exception', function () {
    $mock = new MockHandler([
        new Response(404),
    ]);
    $handlerStack = HandlerStack::create($mock);
    $baseUrl = 'https://api.genesis.com/v1/';
    $httpClient = new Client([
        'handler' => $handlerStack,
        'base_uri' => $baseUrl,
    ]);

    $client = new GenesisClient('test_api_key', $baseUrl, $httpClient);
    $client->get('not-found-endpoint');
})->throws(NotFoundException::class);

it('throws validation exception', function () {
    $errors = ['field' => ['error message']];
    $mock = new MockHandler([
        new Response(422, [], json_encode(['errors' => $errors])),
    ]);
    $handlerStack = HandlerStack::create($mock);
    $baseUrl = 'https://api.genesis.com/v1/';
    $httpClient = new Client([
        'handler' => $handlerStack,
        'base_uri' => $baseUrl,
    ]);

    $client = new GenesisClient('test_api_key', $baseUrl, $httpClient);
    try {
        $client->post('validation-error-endpoint', []);
    } catch (ValidationException $e) {
        expect($e->errors)->toBe($errors);
        throw $e;
    }
})->throws(ValidationException::class);

it('throws server exception', function () {
    $mock = new MockHandler([
        new Response(500),
    ]);
    $handlerStack = HandlerStack::create($mock);
    $baseUrl = 'https://api.genesis.com/v1/';
    $httpClient = new Client([
        'handler' => $handlerStack,
        'base_uri' => $baseUrl,
    ]);

    $client = new GenesisClient('test_api_key', 'some-url.com', $httpClient);
    $client->get('server-error-endpoint');
})->throws(ServerException::class);
