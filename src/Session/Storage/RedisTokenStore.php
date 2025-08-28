<?php

namespace Streeboga\Genesis\Session\Storage;

class RedisTokenStore implements TokenStoreInterface
{
    private $client;
    private string $prefix;

    public function __construct($client, string $prefix = 'genesis:session:')
    {
        $this->client = $client;
        $this->prefix = $prefix;
    }

    public function save(string $sessionId, array $data): void
    {
        $key = $this->prefix . $sessionId;
        $value = json_encode($data);
        if (method_exists($this->client, 'set')) {
            $this->client->set($key, $value);
            return;
        }
        // Predis uses set as well, ext-redis has set
        throw new \RuntimeException('Redis client does not support set method');
    }

    public function load(string $sessionId): ?array
    {
        $key = $this->prefix . $sessionId;
        if (method_exists($this->client, 'get')) {
            $raw = $this->client->get($key);
            if ($raw === null || $raw === false) return null;
            return json_decode($raw, true);
        }
        throw new \RuntimeException('Redis client does not support get method');
    }

    public function delete(string $sessionId): void
    {
        $key = $this->prefix . $sessionId;
        if (method_exists($this->client, 'del')) {
            // ext-redis: del returns int
            $this->client->del($key);
            return;
        }
        if (method_exists($this->client, 'del')) {
            $this->client->del([$key]);
            return;
        }
        if (method_exists($this->client, 'delete')) {
            $this->client->delete($key);
            return;
        }
        throw new \RuntimeException('Redis client does not support del/delete method');
    }

    public function listSessionIds(): array
    {
        // keys pattern
        $pattern = $this->prefix . '*';
        if (method_exists($this->client, 'keys')) {
            $keys = $this->client->keys($pattern);
            if (empty($keys)) return [];
            $ids = array_map(function ($k) {
                return substr($k, strlen($this->prefix));
            }, $keys);
            return $ids;
        }

        throw new \RuntimeException('Redis client does not support keys method');
    }
}






