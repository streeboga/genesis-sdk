<?php

namespace Streeboga\Genesis\Webhook;

use Streeboga\Genesis\Exceptions\WebhookException;

class Client
{
    public function __construct(private string $secret, private string $signatureHeader = 'Content-HMAC')
    {
    }

    public function handleRequest(array $headers, string $body, callable $onEvent): void
    {
        $header = '';
        $key = $this->signatureHeader;

        // headers maybe with lowercase or uppercase keys
        foreach ($headers as $k => $v) {
            if (strtolower($k) === strtolower($key)) {
                $header = is_array($v) ? reset($v) : $v;
                break;
            }
        }

        if ($header === '') {
            throw new WebhookException("Signature header '{$this->signatureHeader}' not found");
        }

        $handler = new Handler($this->secret);
        $handler->handle($body, $header, $onEvent);
    }
}


