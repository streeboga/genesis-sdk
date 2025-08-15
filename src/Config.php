<?php

namespace Streeboga\Genesis;

class Config
{
    public function __construct(
        private string $apiKey,
        private string $baseUrl = 'https://api.genesis.com/v1/'
    ) {
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public static function fromArray(array $data): self
    {
        $apiKey = $data['api_key'] ?? $data['API_KEY'] ?? '';
        $baseUrl = $data['base_url'] ?? $data['BASE_URL'] ?? 'https://api.genesis.com/v1/';

        return new self($apiKey, $baseUrl);
    }

    public static function fromEnvFile(string $path): self
    {
        $data = [];
        if (!file_exists($path)) {
            throw new \InvalidArgumentException("Env file not found: {$path}");
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            [$key, $value] = array_map('trim', explode('=', $line, 2) + [1 => '']);
            $data[$key] = trim($value, " \t\n\r\0\x0B\"");
        }

        return self::fromArray($data);
    }
}
