<?php

require __DIR__ . '/../vendor/autoload.php';

use Streeboga\Genesis\Config;
use Streeboga\Genesis\GenesisClient;

$config = Config::fromArray([
    'api_key' => 'YOUR_API_KEY',
    'base_url' => 'https://api.genesis.com/v1/',
]);

$client = GenesisClient::fromConfig($config);

$projectId = 1;
$userId = 1;

try {
    $planFeatures = $client->billing->getPlanFeatures($projectId, 'plan-1');
    print_r($planFeatures);

    $overage = $client->billing->calculateOveragePrice($projectId, 'plan-1', 'api_calls', 10);
    print_r($overage);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
