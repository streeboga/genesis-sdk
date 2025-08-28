<?php

namespace Streeboga\Genesis\Http;

class RetryPolicy
{
    public function __construct(
        public int $maxRetries = 2,
        public int $baseDelayMs = 100,
        public int $maxDelayMs = 2000
    ) {}

    public function computeDelayMs(int $attempt, ?int $retryAfterSec = null): int
    {
        if ($retryAfterSec !== null) return max(0, $retryAfterSec * 1000);
        $delay = (int) (min($this->maxDelayMs, $this->baseDelayMs * (2 ** ($attempt - 1))));
        return max(0, $delay);
    }
}






