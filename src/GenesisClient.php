<?php

namespace Streeboga\Genesis;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException as GuzzleServerException;
use Streeboga\Genesis\Exceptions\ApiException;
use Streeboga\Genesis\Exceptions\NotFoundException;
use Streeboga\Genesis\Exceptions\ServerException;
use Streeboga\Genesis\Exceptions\ValidationException;

class GenesisClient
{
    private Client $httpClient;

    public readonly FeaturesClient $features;
    public readonly DemoClient $demo;
    public readonly AuthClient $auth;
    public readonly BillingClient $billing;

    public function __construct(
        private string $apiKey,
        private string $baseUrl = 'https://api.genesis.com/v1/',
        ?Client $httpClient = null
    ) {
        $this->httpClient = $httpClient ?? new Client([
            'base_uri' => $this->baseUrl,
            'timeout' => 5,
        ]);

        $this->features = new FeaturesClient($this);
        $this->demo = new DemoClient($this);
        $this->auth = new AuthClient($this);
        $this->billing = new BillingClient($this);
    }

    public static function fromConfig(\Streeboga\Genesis\Config $config, ?Client $httpClient = null): self
    {
        return new self($config->getApiKey(), $config->getBaseUrl(), $httpClient);
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    public function get(string $uri): array
    {
        return $this->request('GET', $uri);
    }

    public function post(string $uri, array $data): array
    {
        return $this->request('POST', $uri, [
            'json' => $data,
        ]);
    }

    public function put(string $uri, array $data): array
    {
        return $this->request('PUT', $uri, [
            'json' => $data,
        ]);
    }

    public function delete(string $uri, array $data = []): array
    {
        return $this->request('DELETE', $uri, [
            'json' => $data,
        ]);
    }

    private function request(string $method, string $uri, array $options = []): array
    {
        $options['headers'] = array_merge($options['headers'] ?? [], [
            'X-API-Key' => $this->apiKey,
            'Accept' => 'application/json',
        ]);

        try {
            $response = $this->httpClient->request($method, $uri, $options);
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $body = json_decode($response->getBody()->getContents(), true);

            if ($response->getStatusCode() === 404) {
                throw new NotFoundException($e->getMessage(), $e->getCode(), $e);
            }

            if ($response->getStatusCode() === 422) {
                throw new ValidationException(
                    message: $body['message'] ?? 'Validation Failed',
                    errors: $body['errors'] ?? [],
                    previous: $e
                );
            }
            throw new ApiException($e->getMessage(), $e->getCode(), $e);
        } catch (GuzzleServerException $e) {
            throw new ServerException($e->getMessage(), $e->getCode(), $e);
        }

        if ($response->getStatusCode() === 204) {
            return [];
        }

        return json_decode($response->getBody()->getContents(), true);
    }
}
