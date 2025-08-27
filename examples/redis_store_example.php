<?php

require __DIR__ . '/../../vendor/autoload.php';

use Streeboga\Genesis\Config;
use Streeboga\Genesis\GenesisClient;
use Streeboga\Genesis\Session\UserSessionManager;
use Streeboga\Genesis\Session\Storage\RedisTokenStore;

// Create client
$config = Config::fromArray(['api_key' => getenv('GENESIS_API_KEY') ?: 'SERVICE_KEY']);
$client = GenesisClient::fromConfig($config);

// create redis client (example using phpredis)
$redis = new Redis();
$redis->connect('127.0.0.1', 6379);

$store = new RedisTokenStore($redis, 'genesis:session:');

$manager = new UserSessionManager($client);
$manager->setStore($store);

// Save session
$manager->setAccessTokenForTesting('access', 'refresh', 3600);
$manager->saveSession('sess_1');

// List
print_r($manager->listSessions());

// Load
$m2 = new UserSessionManager($client);
$m2->setStore($store);
$m2->loadSession('sess_1');
print_r($m2->getAccessToken());

// Revoke
$m2->revokeSession('sess_1');
print_r($m2->listSessions());

echo "Redis example finished.\n";


