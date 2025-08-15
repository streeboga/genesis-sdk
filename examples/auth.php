<?php

require __DIR__ . '/../vendor/autoload.php';

use Streeboga\Genesis\Config;
use Streeboga\Genesis\GenesisClient;

$config = Config::fromArray([
    'api_key' => 'YOUR_API_KEY',
    'base_url' => 'https://api.genesis.com/v1/',
]);

$client = GenesisClient::fromConfig($config);

// Login example
try {
    $res = $client->auth->login(['email' => 'user@example.com', 'password' => 'secret']);
    print_r($res);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
