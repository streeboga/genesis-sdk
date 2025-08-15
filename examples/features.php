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
    $features = $client->features->getFeatures($projectId, $userId);
    print_r($features);

    $check = $client->features->checkFeature($projectId, $userId, 'api-calls');
    print_r($check);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
