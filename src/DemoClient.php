<?php

namespace Streeboga\Genesis;

class DemoClient
{
    public function __construct(private GenesisClient $client)
    {
    }

    public function getStatus(int|string $project, int|string $user): array
    {
        return $this->client->get("projects/{$project}/users/{$user}/demo/status");
    }

    public function giveDemo(int|string $project, int|string $user, string $type = 'standard', ?int $days = null): array
    {
        $payload = ['type' => $type];

        if ($days) {
            $payload['days'] = $days;
        }

        return $this->client->post("projects/{$project}/users/{$user}/demo/give", $payload);
    }

    public function extendDemo(int|string $project, int|string $user, string $feature, int $days): array
    {
        return $this->client->post("projects/{$project}/users/{$user}/demo/extend", [
            'feature' => $feature,
            'days' => $days,
        ]);
    }

    public function revokeDemo(int|string $project, int|string $user, ?string $feature = null): array
    {
        $payload = [];

        if ($feature) {
            $payload['feature'] = $feature;
        }

        return $this->client->delete("projects/{$project}/users/{$user}/demo/revoke", $payload);
    }
}
