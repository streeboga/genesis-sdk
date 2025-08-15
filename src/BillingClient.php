<?php

namespace Streeboga\Genesis;

class BillingClient
{
    public function __construct(private GenesisClient $client)
    {
    }

    public function createSubscription(int|string $project, array $data): array
    {
        return $this->client->post("projects/{$project}/subscriptions", $data);
    }

    public function initiatePayment(array $data): array
    {
        return $this->client->post('payments', $data);
    }

    public function getPlanFeatures(int|string $project, string $planUuid): array
    {
        return $this->client->get("projects/{$project}/plans/{$planUuid}/features");
    }

    public function getPlanMetadata(int|string $project, string $planUuid): array
    {
        return $this->client->get("projects/{$project}/plans/{$planUuid}/metadata");
    }

    public function getOverage(int|string $project, int|string $user): array
    {
        return $this->client->get("projects/{$project}/users/{$user}/overage");
    }

    public function consumeOverage(int|string $project, int|string $user, string $feature, int $amount): array
    {
        return $this->client->post("projects/{$project}/users/{$user}/overage/consume", [
            'feature' => $feature,
            'amount' => $amount,
        ]);
    }

    public function calculateOveragePrice(int|string $project, string $planUuid, string $feature, int $amount): array
    {
        return $this->client->post("projects/{$project}/plans/{$planUuid}/overage/calc", [
            'feature' => $feature,
            'amount' => $amount,
        ]);
    }
}
