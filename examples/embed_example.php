<?php

require __DIR__ . '/../../vendor/autoload.php';

use Streeboga\Genesis\Config;
use Streeboga\Genesis\GenesisClient;

$config = Config::fromArray([
    'api_key' => getenv('GENESIS_API_KEY') ?: 'SERVICE_KEY',
    'base_url' => 'https://api.genesis.com/v1/'
]);
$client = GenesisClient::fromConfig($config);

$widgetUuid = 'your-widget-uuid';

// 1) Get widget info and configuration
$info = $client->embed->getInfo($widgetUuid);
print_r($info);

$configData = $client->embed->getConfig($widgetUuid);
print_r($configData);

// 2) Get asset bodies (CSS / JS)
$css = $client->embed->getStyle($widgetUuid);
echo "--- CSS ---\n" . $css . "\n";

$js = $client->embed->getScript($widgetUuid);
echo "--- JS ---\n" . $js . "\n";

// 3) Widget auth (login as widget user)
$login = $client->embed->authLogin($widgetUuid, ['email' => 'user@example.com', 'password' => 'secret']);
print_r($login);

// 4) Profile & sessions (widget endpoints)
$profile = $client->embed->usersProfile($widgetUuid);
print_r($profile);

$sessions = $client->embed->sessionsList($widgetUuid, []);
print_r($sessions);

// 5) Revoke session example
// $client->embed->revokeSession($widgetUuid, 'session-id');

echo "Embed example finished.\n";






