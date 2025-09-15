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

    public function initiatePayment(int|string|array $project, ?array $data = null): array
    {
        // Backward compatibility: если передан только массив с project_uuid
        if (is_array($project) && $data === null) {
            $projectUuid = $project['project_uuid'] ?? null;
            if (!$projectUuid) {
                throw new \InvalidArgumentException('project_uuid is required when passing data as first parameter');
            }
            return $this->client->post("projects/{$projectUuid}/payments", $project);
        }
        
        // Новый формат: отдельные параметры
        return $this->client->post("projects/{$project}/payments", $data);
    }

    public function getPlanFeatures(int|string $project, string $planUuid): array
    {
        return $this->client->get("projects/{$project}/plans/{$planUuid}/features");
    }

    public function getPlanMetadata(int|string $project, string $planUuid): array
    {
        return $this->client->get("projects/{$project}/plans/{$planUuid}/metadata");
    }

    public function listPlans(int|string $project): array
    {
        return $this->client->get("projects/{$project}/subscriptions/plans");
    }

    public function getSubscriptionStatus(int|string $project, int|string $user): array
    {
        return $this->client->get("projects/{$project}/users/{$user}/subscription/status");
    }

    public function chargeProject(int|string $project, array $data): array
    {
        return $this->client->post("projects/{$project}/billing/charge", $data);
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
