<?php

namespace OpenApi\Cache;

/**
 * Cache interface for SDK implementations
 */
interface CacheInterface
{
    /**
     * Retrieve value from cache
     */
    public function get(string $key): mixed;

    /**
     * Save value to cache with TTL
     *
     * @param string $key Cache key
     * @param mixed $value Value to store
     * @param int $ttl Time-to-live in seconds (default: 3600)
     */
    public function save(string $key, mixed $value, int $ttl = 3600): bool;

    /**
     * Delete value from cache
     */
    public function delete(string $key): bool;

    /**
     * Clear all cached values
     */
    public function clear(): bool;
}