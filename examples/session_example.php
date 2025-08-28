<?php

require __DIR__ . '/../../vendor/autoload.php';

use Streeboga\Genesis\Config;
use Streeboga\Genesis\GenesisClient;
use Streeboga\Genesis\Session\UserSessionManager;
use Streeboga\Genesis\Session\Storage\FileTokenStore;

$config = Config::fromArray([
    'api_key' => 'service_key_here',
    'base_url' => 'https://api.genesis.com/v1/',
]);

$client = GenesisClient::fromConfig($config);

// 1) login (example credentials)
$res = $client->auth->login(['email' => 'user@example.com', 'password' => 'password']);

// 2) create session manager
$manager = new UserSessionManager($client);
$manager->setAccessTokenForTesting(
    $res['access_token'] ?? 'demo_access',
    $res['refresh_token'] ?? 'demo_refresh',
    $res['expires_in'] ?? 3600
);

// 3) store persistently
$store = new FileTokenStore(sys_get_temp_dir() . '/genesis_sessions');
$manager->setStore($store);
$manager->saveSession('user_1_session');

// 4) use client as user
$userClient = $manager->getClient();
$profile = $userClient->get('projects/1/users/1/profile');

print_r($profile);






