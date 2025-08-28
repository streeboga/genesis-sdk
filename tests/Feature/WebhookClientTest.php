<?php

use Streeboga\Genesis\Webhook\Client as WebhookClient;
use Streeboga\Genesis\Exceptions\WebhookException;

it('handles request with headers array', function () {
    $secret = 'secret123';
    $payload = json_encode(['event' => 'payment.success', 'data' => ['id' => 'tx-1']]);
    $signature = base64_encode(hash_hmac('sha256', $payload, $secret, true));

    $client = new WebhookClient($secret, 'Content-HMAC');

    $called = false;
    $client->handleRequest(['Content-HMAC' => $signature], $payload, function ($data) use (&$called) {
        expect($data['event'])->toBe('payment.success');
        $called = true;
    });

    expect($called)->toBeTrue();
});

it('throws when signature header missing', function () {
    $this->expectException(WebhookException::class);
    $client = new WebhookClient('secret123', 'Content-HMAC');
    $client->handleRequest([], '{}', function () {});
});






