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
    public readonly UsersClient $users;
    public readonly EmbedClient $embed;

    public function __construct(
        private string $apiKey,
        private string $baseUrl = 'https://api.genesis.com/v1/',
        ?Client $httpClient = null,
        private ?string $accessToken = null
    ) {
        $this->httpClient = $httpClient ?? new Client([
            'base_uri' => $this->baseUrl,
            'timeout' => 5,
        ]);

        $this->features = new FeaturesClient($this);
        $this->demo = new DemoClient($this);
        $this->auth = new AuthClient($this);
        $this->billing = new BillingClient($this);
        $this->users = new UsersClient($this);
        $this->embed = new EmbedClient($this);
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

    public function getRaw(string $uri, array $options = []): string
    {
        if (isset($this->rateLimiter)) {
            $this->rateLimiter->beforeRequest();
        }
        if (isset($this->circuitBreaker) && $this->circuitBreaker->isOpen()) {
            throw new ApiException('Circuit breaker is open');
        }
        $options['headers'] = array_merge($options['headers'] ?? [], [
            'X-API-Key' => $this->apiKey,
            'Accept' => 'application/json',
        ]);

        if (!empty($this->accessToken)) {
            $options['headers']['Authorization'] = 'Bearer ' . $this->accessToken;
        }

        $attempt = 0;
        do {
            try {
                $response = $this->httpClient->request('GET', $uri, $options);
                if (isset($this->circuitBreaker)) $this->circuitBreaker->onSuccess();
                break;
            } catch (ClientException $e) {
                $response = $e->getResponse();
                if ($response && $response->getStatusCode() === 429 && isset($this->retryPolicy)) {
                    $retryAfter = $response->getHeaderLine('Retry-After');
                    $delayMs = $this->retryPolicy->computeDelayMs(++$attempt, $retryAfter !== '' ? (int)$retryAfter : null);
                    if ($attempt <= $this->retryPolicy->maxRetries) { usleep($delayMs * 1000); continue; }
                }
                if (isset($this->circuitBreaker)) $this->circuitBreaker->onFailure();
                throw new ApiException($e->getMessage(), $e->getCode(), $e);
            } catch (GuzzleServerException $e) {
                if (isset($this->retryPolicy) && $attempt < $this->retryPolicy->maxRetries) {
                    $delayMs = $this->retryPolicy->computeDelayMs(++$attempt, null);
                    usleep($delayMs * 1000);
                    continue;
                }
                if (isset($this->circuitBreaker)) $this->circuitBreaker->onFailure();
                throw new ServerException($e->getMessage(), $e->getCode(), $e);
            }
        } while (true);

        return $response->getBody()->getContents();
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
        if (isset($this->rateLimiter)) {
            $this->rateLimiter->beforeRequest();
        }
        if (isset($this->circuitBreaker) && $this->circuitBreaker->isOpen()) {
            throw new ApiException('Circuit breaker is open');
        }
        $options['headers'] = array_merge($options['headers'] ?? [], [
            'X-API-Key' => $this->apiKey,
            'Accept' => 'application/json',
        ]);

        if (!empty($this->accessToken)) {
            $options['headers']['Authorization'] = 'Bearer ' . $this->accessToken;
        }

        $attempt = 0;
        do {
            try {
                $response = $this->httpClient->request($method, $uri, $options);
                if (isset($this->circuitBreaker)) $this->circuitBreaker->onSuccess();
                break;
            } catch (ClientException $e) {
                $response = $e->getResponse();
                $body = $response ? json_decode($response->getBody()->getContents(), true) : null;

                if ($response && $response->getStatusCode() === 404) {
                    throw new NotFoundException($e->getMessage(), $e->getCode(), $e);
                }

                if ($response && $response->getStatusCode() === 422) {
                    throw new ValidationException(
                        message: $body['message'] ?? 'Validation Failed',
                        errors: $body['errors'] ?? [],
                        previous: $e
                    );
                }

                if ($response && $response->getStatusCode() === 429 && isset($this->retryPolicy)) {
                    $retryAfter = $response->getHeaderLine('Retry-After');
                    $delayMs = $this->retryPolicy->computeDelayMs(++$attempt, $retryAfter !== '' ? (int)$retryAfter : null);
                    if ($attempt <= $this->retryPolicy->maxRetries) { usleep($delayMs * 1000); continue; }
                }
                if (isset($this->circuitBreaker)) $this->circuitBreaker->onFailure();
                throw new ApiException($e->getMessage(), $e->getCode(), $e);
            } catch (GuzzleServerException $e) {
                if (isset($this->retryPolicy) && $attempt < $this->retryPolicy->maxRetries) {
                    $delayMs = $this->retryPolicy->computeDelayMs(++$attempt, null);
                    usleep($delayMs * 1000);
                    continue;
                }
                if (isset($this->circuitBreaker)) $this->circuitBreaker->onFailure();
                throw new ServerException($e->getMessage(), $e->getCode(), $e);
            }
        } while (true);

        if ($response->getStatusCode() === 204) {
            return [];
        }

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Return a clone of client that will use provided access token for Authorization header
     */
    public function asUser(string $accessToken): self
    {
        // clone shallow, but set access token
        $clone = new self($this->apiKey, $this->baseUrl, $this->httpClient, $accessToken);
        return $clone;
    }

    // Resilience configuration
    private ?\Streeboga\Genesis\Http\RetryPolicy $retryPolicy = null;
    private ?\Streeboga\Genesis\Http\RateLimiter $rateLimiter = null;
    private ?\Streeboga\Genesis\Http\CircuitBreaker $circuitBreaker = null;

    public function withRetryPolicy(\Streeboga\Genesis\Http\RetryPolicy $policy): self
    {
        $this->retryPolicy = $policy;
        return $this;
    }

    public function withRateLimiter(\Streeboga\Genesis\Http\RateLimiter $limiter): self
    {
        $this->rateLimiter = $limiter;
        return $this;
    }

    public function withCircuitBreaker(\Streeboga\Genesis\Http\CircuitBreaker $breaker): self
    {
        $this->circuitBreaker = $breaker;
        return $this;
    }
}
