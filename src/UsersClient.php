<?php

namespace Streeboga\Genesis;

class UsersClient
{
    public function __construct(private GenesisClient $client)
    {
    }

    public function getProfile(int|string $project, int|string $user): array
    {
        return $this->client->get("projects/{$project}/users/{$user}/profile");
    }

    public function updateProfile(int|string $project, int|string $user, array $data): array
    {
        return $this->client->put("projects/{$project}/users/{$user}/profile", $data);
    }

    public function deleteAccount(int|string $project, int|string $user): array
    {
        return $this->client->delete("projects/{$project}/users/{$user}/account");
    }

    public function listSessions(int|string $project, int|string $user, array $params = []): array
    {
        return $this->client->post("projects/{$project}/users/{$user}/sessions", $params);
    }

    public function revokeSession(int|string $project, int|string $user, string $sessionId): array
    {
        return $this->client->delete("projects/{$project}/users/{$user}/sessions/{$sessionId}");
    }
}
