<?php

namespace Streeboga\Genesis\Http;

class CircuitBreaker
{
    private int $failureCount = 0;
    private ?int $openedAt = null;

    public function __construct(
        public int $failureThreshold = 5,
        public int $resetTimeoutSec = 60
    ) {}

    public function onSuccess(): void
    {
        $this->failureCount = 0;
        $this->openedAt = null;
    }

    public function onFailure(): void
    {
        $this->failureCount++;
        if ($this->failureCount >= $this->failureThreshold) {
            $this->openedAt = time();
        }
    }

    public function isOpen(): bool
    {
        if ($this->openedAt === null) return false;
        $elapsed = time() - $this->openedAt;
        return $elapsed < $this->resetTimeoutSec;
    }
}


