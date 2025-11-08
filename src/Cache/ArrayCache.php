<?php

namespace OpenApi\Cache;

class ArrayCache implements CacheInterface
{
    private array $cache = [];
    private array $expiry = [];

    public function get(string $key): mixed
    {
        if (!isset($this->cache[$key])) {
            return null;
        }

        if (isset($this->expiry[$key]) && time() > $this->expiry[$key]) {
            $this->delete($key);
            return null;
        }

        return $this->cache[$key];
    }

    public function save(string $key, mixed $value, int $ttl = 3600): bool
    {
        $this->cache[$key] = $value;
        $this->expiry[$key] = time() + $ttl;
        
        return true;
    }

    public function delete(string $key): bool
    {
        unset($this->cache[$key], $this->expiry[$key]);
        
        return true;
    }

    public function clear(): bool
    {
        $this->cache = [];
        $this->expiry = [];
        
        return true;
    }
}