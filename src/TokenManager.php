<?php

namespace Openapi;

use Illuminate\Contracts\Cache\Lock;
use Illuminate\Contracts\Cache\LockProvider;
use Illuminate\Contracts\Cache\Repository as CacheRepository;

/**
 * Requires Laravel's cache (illuminate/cache) and a store that supports atomic locks
 * (redis, memcached, database, file, array...). Under Octane use a store shared between workers.
 */
class TokenManager
{
    private const LOCK_POLL_MICROSECONDS = 250000;

    private LockProvider $locks;

    /**
     * @param int $refreshSkew Seconds before expiry at which a token starts being refreshed. It is
     *                         capped at half of the token's lifetime so short-lived tokens still get cached.
     * @param int $lockSeconds Maximum lifetime of the lock (should exceed the OAuth request timeout).
     * @param int $lockWait    Seconds to wait for another process to obtain the token when none exists.
     */
    public function __construct(
        private OauthClient $oauth,
        private CacheRepository $cache,
        private string $prefix = 'openapi:token:',
        private int $refreshSkew = 300,
        private int $lockSeconds = 45,
        private int $lockWait = 15
    ) {
        $store = $cache->getStore();

        if (!$store instanceof LockProvider) {
            throw new \InvalidArgumentException(
                'The cache store does not support atomic locks. '
                . 'Use a lock-capable store such as redis, memcached, database, file or array.'
            );
        }

        $this->locks = $store;
    }

    public function getToken(array $scopes, int $ttl = 3600): string
    {
        if ($ttl < 1) {
            throw new \InvalidArgumentException('The token ttl must be at least 1 second.');
        }

        $key = $this->cacheKey($scopes);
        $entry = $this->read($key);

        if ($entry !== null && $entry['refresh_at'] > time()) {
            return $entry['token'];
        }

        $lock = $this->locks->lock($key . ':lock', $this->lockSeconds);

        if ($entry !== null) {
            if (!$lock->get()) {
                return $entry['token'];
            }
        } elseif (!$this->waitFor($lock)) {
            $entry = $this->read($key);
            if ($entry !== null) {
                return $entry['token'];
            }

            throw new ApiException('Timed out waiting for another process to obtain an access token.');
        }

        try {
            return $this->refresh($key, $scopes, $ttl);
        } finally {
            $lock->release();
        }
    }

    private function refresh(string $key, array $scopes, int $ttl): string
    {
        $current = $this->read($key);
        if ($current !== null && $current['refresh_at'] > time()) {
            return $current['token'];
        }

        try {
            $entry = $this->requestToken($scopes, $ttl);
        } catch (\Throwable $e) {
            if ($current !== null) {
                return $current['token'];
            }

            throw $e;
        }

        $this->cache->put($key, $entry, max(1, $entry['expires_at'] - time()));

        return $entry['token'];
    }

    private function requestToken(array $scopes, int $ttl): array
    {
        $response = $this->oauth->createToken($scopes, $ttl);
        $data = json_decode($response, true);

        if (!is_array($data) || empty($data['token']) || !is_string($data['token'])) {
            throw new ApiException('Failed to generate token: ' . $response);
        }

        $now = time();
        $expiresAt = $now + $ttl;

        if (isset($data['expire']) && is_numeric($data['expire']) && (int) $data['expire'] > $now) {
            $expiresAt = min($expiresAt, (int) $data['expire']);
        }

        $lifetime = $expiresAt - $now;

        return [
            'token' => $data['token'],
            'expires_at' => $expiresAt,
            'refresh_at' => $expiresAt - min($this->refreshSkew, intdiv($lifetime, 2)),
        ];
    }

    private function waitFor(Lock $lock): bool
    {
        $deadline = microtime(true) + max(0, $this->lockWait);

        do {
            if ($lock->get()) {
                return true;
            }

            usleep(self::LOCK_POLL_MICROSECONDS);
        } while (microtime(true) < $deadline);

        return false;
    }

    private function read(string $key): ?array
    {
        $entry = $this->cache->get($key);

        if (
            !is_array($entry)
            || !isset($entry['token'], $entry['expires_at'], $entry['refresh_at'])
            || !is_string($entry['token'])
            || !is_int($entry['expires_at'])
            || !is_int($entry['refresh_at'])
            || $entry['expires_at'] <= time()
        ) {
            return null;
        }

        return [
            'token' => $entry['token'],
            'expires_at' => $entry['expires_at'],
            'refresh_at' => $entry['refresh_at'],
        ];
    }

    private function cacheKey(array $scopes): string
    {
        $sorted = array_values(array_unique($scopes));
        sort($sorted);

        return $this->prefix . sha1((string) json_encode([
            $this->oauth->getBaseUrl(),
            $this->oauth->getUsername(),
            $sorted,
        ]));
    }
}
