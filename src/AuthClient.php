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

    public function forgotPassword(array $data): array
    {
        return $this->client->post('auth/password/forgot', $data);
    }

    public function resetPassword(array $data): array
    {
        return $this->client->post('auth/password/reset', $data);
    }

    public function resendVerification(array $data): array
    {
        return $this->client->post('auth/resend-verification', $data);
    }

    public function verifyEmail(array $data): array
    {
        return $this->client->post('auth/email/verify', $data);
    }

    public function twoFaChallenge(array $data): array
    {
        return $this->client->post('auth/2fa/challenge', $data);
    }

    public function twoFaVerify(array $data): array
    {
        return $this->client->post('auth/2fa/verify', $data);
    }
}
