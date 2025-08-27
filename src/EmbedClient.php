<?php

namespace Streeboga\Genesis;

class EmbedClient
{
    public function __construct(private GenesisClient $client)
    {
    }

    public function getInfo(string $uuid): array
    {
        return $this->client->get("embed/widgets/{$uuid}");
    }

    public function getConfig(string $uuid): array
    {
        return $this->client->get("embed/widgets/{$uuid}/config");
    }

    public function getStyle(string $uuid): string
    {
        return $this->client->getRaw("embed/widgets/{$uuid}/assets/styles.css");
    }

    public function getScript(string $uuid): string
    {
        return $this->client->getRaw("embed/widgets/{$uuid}/assets/script.js");
    }

    public function health(string $uuid): array
    {
        return $this->client->get("embed/widgets/{$uuid}/health");
    }

    public function authLogin(string $uuid, array $credentials): array
    {
        return $this->client->post("embed/widgets/{$uuid}/auth/login", $credentials);
    }

    public function usersProfile(string $uuid): array
    {
        return $this->client->get("embed/widgets/{$uuid}/users/profile");
    }

    public function sessionsList(string $uuid, array $params = []): array
    {
        return $this->client->post("embed/widgets/{$uuid}/users/sessions", $params);
    }

    public function revokeSession(string $uuid, string $sessionId): array
    {
        return $this->client->delete("embed/widgets/{$uuid}/users/sessions/{$sessionId}");
    }
}
