<?php

namespace OpenApi\Cache;

interface CacheInterface
{
    public function get(string $key): mixed;
    public function save(string $key, mixed $value, int $ttl = 3600): bool;
    public function delete(string $key): bool;
    public function clear(): bool;
}