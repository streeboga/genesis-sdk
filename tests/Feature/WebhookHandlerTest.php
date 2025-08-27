<?php

use Streeboga\Genesis\Webhook\Handler;
use Streeboga\Genesis\Exceptions\WebhookException;

it('verifies correct signature', function () {
    $secret = 'secret123';
    $payload = json_encode(['event' => 'payment.success', 'data' => ['id' => 'tx-1']]);
    $signature = base64_encode(hash_hmac('sha256', $payload, $secret, true));

    $handler = new Handler($secret);
    expect($handler->verifySignature($payload, $signature))->toBeTrue();
});

it('throws on invalid signature', function () {
    $this->expectException(WebhookException::class);
    $secret = 'secret123';
    $payload = json_encode(['event' => 'payment.success']);

    $handler = new Handler($secret);
    $handler->handle($payload, 'invalid-signature', function ($data) {
        // nothing
    });
});

it('parses and dispatches event', function () {
    $secret = 'secret123';
    $payload = json_encode(['event' => 'payment.success', 'data' => ['id' => 'tx-1']]);
    $signature = base64_encode(hash_hmac('sha256', $payload, $secret, true));

    $handler = new Handler($secret);

    $called = false;
    $handler->handle($payload, $signature, function ($data) use (&$called) {
        expect($data['event'])->toBe('payment.success');
        $called = true;
    });

    expect($called)->toBeTrue();
});


