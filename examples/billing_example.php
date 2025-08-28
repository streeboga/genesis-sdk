<?php

require __DIR__ . '/../../vendor/autoload.php';

use Streeboga\Genesis\Config;
use Streeboga\Genesis\GenesisClient;

$config = Config::fromArray([
    'api_key' => getenv('GENESIS_API_KEY') ?: 'SERVICE_KEY',
    'base_url' => 'https://api.genesis.com/v1/'
]);
$client = GenesisClient::fromConfig($config);

$projectId = 1;
$planUuid = 'plan-uuid-123';

// List plans
$plans = $client->billing->listPlans($projectId);
print_r($plans);

// Get plan metadata and features
$meta = $client->billing->getPlanMetadata($projectId, $planUuid);
print_r($meta);

$features = $client->billing->getPlanFeatures($projectId, $planUuid);
print_r($features);

// Calculate overage price
$price = $client->billing->calculateOveragePrice($projectId, $planUuid, 'api_calls', 100);
print_r($price);

echo "Billing example finished.\n";






