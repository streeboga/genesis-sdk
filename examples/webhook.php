<?php

require __DIR__ . '/../vendor/autoload.php';

use Streeboga\Genesis\Webhook\Client as WebhookClient;

$secret = 'your-webhook-secret';
$headers = getallheaders();
$body = file_get_contents('php://input');

$client = new WebhookClient($secret, 'Content-HMAC');

try {
    $client->handleRequest($headers, $body, function ($event) {
        // handle event
        error_log('Received webhook: ' . json_encode($event));
        http_response_code(200);
        echo 'OK';
    });
} catch (Exception $e) {
    http_response_code(400);
    echo $e->getMessage();
}






