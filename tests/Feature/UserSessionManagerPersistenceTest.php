<?php

use Streeboga\Genesis\GenesisClient;
use Streeboga\Genesis\Session\UserSessionManager;
use Streeboga\Genesis\Session\Storage\FileTokenStore;

it('saves loads lists and revokes sessions via file store', function () {
    $baseUrl = 'https://api.genesis.com/v1/';
    $httpClient = new \GuzzleHttp\Client(['base_uri' => $baseUrl]);
    $config = \Streeboga\Genesis\Config::fromArray(['api_key' => 'service_key', 'base_url' => $baseUrl]);
    $serviceClient = GenesisClient::fromConfig($config, $httpClient);

    $tmp = sys_get_temp_dir() . '/genesis_sessions_' . uniqid();
    $store = new FileTokenStore($tmp);

    $manager = new UserSessionManager($serviceClient, fn() => time(), 1);
    $manager->setAccessTokenForTesting('a', 'r', 3600);
    $manager->setStore($store);

    $manager->saveSession('sess1');
    expect($manager->listSessions())->toContain('sess1');

    // load into a new manager
    $m2 = new UserSessionManager($serviceClient, fn() => time(), 1);
    $m2->setStore($store);
    $ok = $m2->loadSession('sess1');
    expect($ok)->toBeTrue();
    expect($m2->getAccessToken())->toBe('a');

    $m2->revokeSession('sess1');
    expect($m2->listSessions())->toBe([]);

    // cleanup
    @rmdir($tmp);
});






