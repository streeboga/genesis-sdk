<?php

namespace Streeboga\Genesis;

class AuthClient
{
    public function __construct(private GenesisClient $client)
    {
    }

    public function login(array $credentials): array
    {
        return $this->client->post('auth/login', $credentials);
    }

    public function register(array $data): array
    {
        return $this->client->post('auth/register', $data);
    }

    public function refresh(string $refreshToken): array
    {
        return $this->client->post('auth/refresh', ['refresh_token' => $refreshToken]);
    }

    public function logout(): array
    {
        return $this->client->post('auth/logout', []);
    }
}
