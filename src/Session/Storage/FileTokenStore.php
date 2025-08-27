<?php

namespace Streeboga\Genesis\Session\Storage;

class FileTokenStore implements TokenStoreInterface
{
    private string $dir;

    public function __construct(string $dir)
    {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $this->dir = rtrim($dir, DIRECTORY_SEPARATOR);
    }

    public function save(string $sessionId, array $data): void
    {
        file_put_contents($this->path($sessionId), json_encode($data));
    }

    public function load(string $sessionId): ?array
    {
        $p = $this->path($sessionId);
        if (!file_exists($p)) {
            return null;
        }
        return json_decode(file_get_contents($p), true);
    }

    public function delete(string $sessionId): void
    {
        $p = $this->path($sessionId);
        if (file_exists($p)) {
            unlink($p);
        }
    }

    public function listSessionIds(): array
    {
        $files = glob($this->dir . DIRECTORY_SEPARATOR . '*.json');
        return array_map(fn($f) => basename($f, '.json'), $files);
    }

    private function path(string $sessionId): string
    {
        return $this->dir . DIRECTORY_SEPARATOR . $sessionId . '.json';
    }
}


