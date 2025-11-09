<?php

namespace OpenApi\Cache;

/**
 * In-memory cache implementation
 * Data is stored in PHP arrays and cleared at end of script execution
 */
class ArrayCache implements CacheInterface
{
    private array $cache = [];
    private array $expiry = [];

    /**
     * Retrieve value from cache
     * Returns null if key doesn't exist or has expired
     */
    public function get(string $key): mixed
    {
        if (!isset($this->cache[$key])) {
            return null;
        }

        // Check if expired
        if (isset($this->expiry[$key]) && time() > $this->expiry[$key]) {
            $this->delete($key);
            return null;
        }

        return $this->cache[$key];
    }

    /**
     * Save value to cache with expiration
     */
    public function save(string $key, mixed $value, int $ttl = 3600): bool
    {
        $this->cache[$key] = $value;
        $this->expiry[$key] = time() + $ttl;

        return true;
    }

    /**
     * Delete value from cache
     */
    public function delete(string $key): bool
    {
        unset($this->cache[$key], $this->expiry[$key]);

        return true;
    }

    /**
     * Clear all cached values
     */
    public function clear(): bool
    {
        $this->cache = [];
        $this->expiry = [];

        return true;
    }
}