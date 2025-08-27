<?php

namespace Streeboga\Genesis\Session;

use Streeboga\Genesis\GenesisClient;

class UserSessionManager
{
    private ?string $accessToken = null;
    private ?string $refreshToken = null;
    private ?int $expiresAt = null; // unix timestamp

    private GenesisClient $client;
    /** @var callable */
    private $timeProvider;
    private int $leeway;
    private ?\Streeboga\Genesis\Session\Storage\TokenStoreInterface $store = null;

    /**
     * @param callable|null $timeProvider returns int unix time
     */
    public function __construct(GenesisClient $client, $timeProvider = null, int $leeway = 30)
    {
        $this->client = $client;
        $this->timeProvider = $timeProvider ?? function () { return time(); };
        $this->leeway = $leeway;
    }

    public function setStore(\Streeboga\Genesis\Session\Storage\TokenStoreInterface $store): void
    {
        $this->store = $store;
    }

    public function saveSession(string $sessionId): void
    {
        if (!$this->store) {
            throw new \RuntimeException('No token store configured');
        }
        $data = [
            'access_token' => $this->accessToken,
            'refresh_token' => $this->refreshToken,
            'expires_at' => $this->expiresAt,
        ];
        $this->store->save($sessionId, $data);
    }

    public function loadSession(string $sessionId): bool
    {
        if (!$this->store) {
            throw new \RuntimeException('No token store configured');
        }
        $data = $this->store->load($sessionId);
        if (empty($data)) return false;
        $this->accessToken = $data['access_token'] ?? null;
        $this->refreshToken = $data['refresh_token'] ?? null;
        $this->expiresAt = $data['expires_at'] ?? null;
        return true;
    }

    public function revokeSession(string $sessionId): void
    {
        if (!$this->store) {
            throw new \RuntimeException('No token store configured');
        }
        // optionally call server to revoke refresh token
        $this->store->delete($sessionId);
    }

    public function listSessions(): array
    {
        if (!$this->store) {
            return [];
        }
        return $this->store->listSessionIds();
    }

    public static function fromLogin(GenesisClient $client, array $credentials, ?callable $timeProvider = null): self
    {
        $manager = new self($client, $timeProvider);
        $res = $client->auth->login($credentials);
        $manager->setTokensFromResponse($res);
        return $manager;
    }

    private function setTokensFromResponse(array $res): void
    {
        $this->accessToken = $res['access_token'] ?? null;
        $this->refreshToken = $res['refresh_token'] ?? null;
        $expiresIn = isset($res['expires_in']) ? (int)$res['expires_in'] : null;
        $now = ($this->timeProvider)();
        $this->expiresAt = $expiresIn ? $now + $expiresIn : null;
    }

    public function ensureValidAccessToken(): void
    {
        if (empty($this->accessToken)) {
            throw new \RuntimeException('No access token present');
        }

        $now = ($this->timeProvider)();
        if ($this->expiresAt !== null && $now >= ($this->expiresAt - $this->leeway)) {
            // refresh
            if (empty($this->refreshToken)) {
                throw new \RuntimeException('No refresh token available to refresh access token');
            }

            $res = $this->client->auth->refresh($this->refreshToken);
            $this->setTokensFromResponse($res);
        }
    }

    public function getClient(): GenesisClient
    {
        $this->ensureValidAccessToken();
        return $this->client->asUser($this->accessToken);
    }

    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    public function getRefreshToken(): ?string
    {
        return $this->refreshToken;
    }

    // helper for tests
    public function setAccessTokenForTesting(string $accessToken, string $refreshToken, int $expiresIn): void
    {
        $this->accessToken = $accessToken;
        $this->refreshToken = $refreshToken;
        $this->expiresAt = ($this->timeProvider)() + $expiresIn;
    }
}
