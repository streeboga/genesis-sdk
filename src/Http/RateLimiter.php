<?php

namespace Streeboga\Genesis\Http;

class RateLimiter
{
    private float $lastTime = 0.0; // seconds

    public function __construct(public int $minIntervalMs = 0)
    {
    }

    public function beforeRequest(): void
    {
        if ($this->minIntervalMs <= 0) return;
        $now = microtime(true);
        $minIntervalSec = $this->minIntervalMs / 1000.0;
        $nextAllowed = $this->lastTime + $minIntervalSec;
        if ($now < $nextAllowed) {
            $sleepSec = $nextAllowed - $now;
            usleep((int) max(0, $sleepSec * 1_000_000));
            $now = microtime(true);
        }
        $this->lastTime = $now;
    }
}






