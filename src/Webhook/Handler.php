<?php

namespace Streeboga\Genesis\Webhook;

use Streeboga\Genesis\Exceptions\WebhookException;

class Handler
{
    public function __construct(private string $secret)
    {
    }

    public function verifySignature(string $payload, string $header): bool
    {
        // header contains base64 HMAC-SHA256
        $calculated = base64_encode(hash_hmac('sha256', $payload, $this->secret, true));
        return hash_equals($calculated, $header);
    }

    public function handle(string $payload, string $signatureHeader, callable $onEvent): void
    {
        if (!$this->verifySignature($payload, $signatureHeader)) {
            throw new WebhookException('Invalid signature');
        }

        $data = json_decode($payload, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new WebhookException('Invalid JSON payload');
        }

        // Dispatch to user provided callback
        $onEvent($data);
    }
}


