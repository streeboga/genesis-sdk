<?php

namespace Streeboga\Genesis;

class FeaturesClient
{
    public function __construct(private GenesisClient $client)
    {
    }

    public function getFeatures(int|string $project, int|string $user): array
    {
        return $this->client->get("projects/{$project}/users/{$user}/features");
    }

    public function checkFeature(int|string $project, int|string $user, string $feature): array
    {
        return $this->client->get("projects/{$project}/users/{$user}/features/{$feature}/check");
    }

    public function consumeFeature(int|string $project, int|string $user, string $feature, int $amount = 1): array
    {
        return $this->client->post("projects/{$project}/users/{$user}/features/consume", [
            'feature' => $feature,
            'amount' => $amount,
        ]);
    }

    public function getStats(int|string $project, int|string $user): array
    {
        return $this->client->get("projects/{$project}/users/{$user}/features/stats");
    }

    public function getPricing(int|string $project, int|string $user): array
    {
        return $this->client->get("projects/{$project}/users/{$user}/features/pricing");
    }

    public function consumeWithBalance(int|string $project, int|string $user, string $feature, int $amount = 1): array
    {
        return $this->client->post("projects/{$project}/users/{$user}/features/consume-with-balance", [
            'feature' => $feature,
            'amount' => $amount,
        ]);
    }
}
