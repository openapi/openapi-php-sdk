<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository;
use Illuminate\Contracts\Cache\LockProvider;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Contracts\Cache\Store;
use Openapi\ApiException;
use Openapi\TokenManager;
use PHPUnit\Framework\TestCase;
use Tests\Fakes\CountingOauthClient;
use Tests\Fakes\StaleFirstReadRepository;

final class TokenManagerTest extends TestCase
{
    private const SCOPES = ['GET:test.imprese.openapi.it/advance'];

    private function oauth(): CountingOauthClient
    {
        return new CountingOauthClient('user', 'key', true);
    }

    private function repository(): Repository
    {
        return new Repository(new ArrayStore());
    }

    /**
     * @param list<string> $scopes
     */
    private function keyFor(TokenManager $manager, array $scopes = self::SCOPES): string
    {
        $method = new \ReflectionMethod($manager, 'cacheKey');
        $method->setAccessible(true);

        return $method->invoke($manager, $scopes);
    }

    /**
     * @return array{token: string, expires_at: int, refresh_at: int}
     */
    private function entry(Repository $repo, TokenManager $manager): array
    {
        $entry = $repo->get($this->keyFor($manager));
        $this->assertIsArray($entry);

        return $entry;
    }

    private function locks(Repository $repo): LockProvider
    {
        $store = $repo->getStore();
        $this->assertInstanceOf(LockProvider::class, $store);

        return $store;
    }

    private function holdLock(Repository $repo, TokenManager $manager, int $seconds = 30): void
    {
        $lock = $this->locks($repo)->lock($this->keyFor($manager) . ':lock', $seconds);
        $this->assertTrue($lock->get());
    }

    public function test_token_is_created_once_and_reused(): void
    {
        $oauth = $this->oauth();
        $manager = new TokenManager($oauth, $this->repository());

        $first = $manager->getToken(self::SCOPES);

        $this->assertSame('tok-1', $first);
        $this->assertSame($first, $manager->getToken(self::SCOPES));
        $this->assertSame(1, $oauth->calls);
    }

    public function test_token_is_shared_between_managers_using_the_same_cache(): void
    {
        $repo = $this->repository();
        $a = $this->oauth();
        $b = $this->oauth();

        (new TokenManager($a, $repo))->getToken(self::SCOPES);

        $this->assertSame('tok-1', (new TokenManager($b, $repo))->getToken(self::SCOPES));
        $this->assertSame(0, $b->calls);
    }

    public function test_scope_order_and_duplicates_do_not_change_the_cache_key(): void
    {
        $oauth = $this->oauth();
        $manager = new TokenManager($oauth, $this->repository());

        $manager->getToken(['a', 'b']);
        $manager->getToken(['b', 'a', 'a']);

        $this->assertSame(1, $oauth->calls);
    }

    public function test_different_scopes_get_different_tokens(): void
    {
        $oauth = $this->oauth();
        $manager = new TokenManager($oauth, $this->repository());

        $manager->getToken(['a']);
        $manager->getToken(['b']);

        $this->assertSame(2, $oauth->calls);
    }

    public function test_ttl_is_not_part_of_the_cache_key(): void
    {
        $oauth = $this->oauth();
        $manager = new TokenManager($oauth, $this->repository());

        $manager->getToken(self::SCOPES, 3600);
        $manager->getToken(self::SCOPES, 600);

        $this->assertSame(1, $oauth->calls);
    }

    public function test_expiry_reported_by_the_server_is_used_when_shorter_than_the_ttl(): void
    {
        $repo = $this->repository();
        $oauth = $this->oauth();
        $oauth->expireIn = 1800;
        $manager = new TokenManager($oauth, $repo);

        $manager->getToken(self::SCOPES, 3600);

        $this->assertEqualsWithDelta(time() + 1800, $this->entry($repo, $manager)['expires_at'], 2);
    }

    public function test_requested_ttl_caps_a_longer_server_expiry(): void
    {
        $repo = $this->repository();
        $oauth = $this->oauth();
        $oauth->expireIn = 7200;
        $manager = new TokenManager($oauth, $repo);

        $manager->getToken(self::SCOPES, 3600);

        $this->assertEqualsWithDelta(time() + 3600, $this->entry($repo, $manager)['expires_at'], 2);
    }

    public function test_requested_ttl_is_used_when_the_server_sends_no_usable_expiry(): void
    {
        $repo = $this->repository();
        $oauth = $this->oauth();
        $oauth->omitExpire = true;
        $manager = new TokenManager($oauth, $repo);
        $manager->getToken(self::SCOPES, 900);
        $this->assertEqualsWithDelta(time() + 900, $this->entry($repo, $manager)['expires_at'], 2);

        $repo = $this->repository();
        $oauth = $this->oauth();
        $oauth->expireIn = -10;
        $manager = new TokenManager($oauth, $repo);
        $manager->getToken(self::SCOPES, 900);
        $this->assertEqualsWithDelta(time() + 900, $this->entry($repo, $manager)['expires_at'], 2);
    }

    /**
     * @return array<string, array{int}>
     */
    public function shortLifetimes(): array
    {
        return [
            'equal to refresh_skew' => [300],
            'below refresh_skew' => [120],
            'very short' => [10],
        ];
    }

    /**
     * @dataProvider shortLifetimes
     */
    public function test_tokens_not_longer_than_refresh_skew_are_still_cached(int $lifetime): void
    {
        $oauth = $this->oauth();
        $manager = new TokenManager($oauth, $this->repository());

        for ($i = 0; $i < 10; $i++) {
            $manager->getToken(self::SCOPES, $lifetime);
        }

        $this->assertSame(1, $oauth->calls);
    }

    public function test_refresh_margin_is_capped_at_half_of_the_lifetime(): void
    {
        $repo = $this->repository();
        $manager = new TokenManager($this->oauth(), $repo);
        $manager->getToken(self::SCOPES, 120);
        $entry = $this->entry($repo, $manager);
        $this->assertSame(60, $entry['expires_at'] - $entry['refresh_at']);

        $repo = $this->repository();
        $manager = new TokenManager($this->oauth(), $repo);
        $manager->getToken(self::SCOPES, 3600);
        $entry = $this->entry($repo, $manager);
        $this->assertSame(300, $entry['expires_at'] - $entry['refresh_at']);
    }

    public function test_expired_entry_is_ignored_and_a_new_token_is_requested(): void
    {
        $repo = $this->repository();
        $oauth = $this->oauth();
        $manager = new TokenManager($oauth, $repo);
        $repo->put(
            $this->keyFor($manager),
            ['token' => 'old', 'expires_at' => time() - 10, 'refresh_at' => time() - 100],
            60
        );

        $this->assertSame('tok-1', $manager->getToken(self::SCOPES));
    }

    public function test_malformed_cache_entry_is_ignored(): void
    {
        $repo = $this->repository();
        $oauth = $this->oauth();
        $manager = new TokenManager($oauth, $repo);
        $repo->put($this->keyFor($manager), 'garbage', 60);

        $this->assertSame('tok-1', $manager->getToken(self::SCOPES));
    }

    private function seedDueForRefresh(Repository $repo, TokenManager $manager): void
    {
        $repo->put(
            $this->keyFor($manager),
            ['token' => 'old', 'expires_at' => time() + 100, 'refresh_at' => time() - 1],
            100
        );
    }

    public function test_due_token_is_still_served_while_another_worker_refreshes_it(): void
    {
        $repo = $this->repository();
        $oauth = $this->oauth();
        $manager = new TokenManager($oauth, $repo);
        $this->seedDueForRefresh($repo, $manager);
        $this->holdLock($repo, $manager);

        $this->assertSame('old', $manager->getToken(self::SCOPES));
        $this->assertSame(0, $oauth->calls);
    }

    public function test_due_token_is_refreshed_when_the_lock_is_free(): void
    {
        $repo = $this->repository();
        $oauth = $this->oauth();
        $manager = new TokenManager($oauth, $repo);
        $this->seedDueForRefresh($repo, $manager);

        $first = $manager->getToken(self::SCOPES);

        $this->assertSame('tok-1', $first);
        $this->assertSame($first, $manager->getToken(self::SCOPES));
        $this->assertSame(1, $oauth->calls);
    }

    public function test_failed_refresh_keeps_serving_the_still_valid_token(): void
    {
        $repo = $this->repository();
        $oauth = $this->oauth();
        $oauth->fail = true;
        $manager = new TokenManager($oauth, $repo);
        $this->seedDueForRefresh($repo, $manager);

        $this->assertSame('old', $manager->getToken(self::SCOPES));
    }

    public function test_failure_without_any_token_is_thrown_and_the_lock_is_released(): void
    {
        $repo = $this->repository();
        $oauth = $this->oauth();
        $oauth->fail = true;
        $manager = new TokenManager($oauth, $repo);

        try {
            $manager->getToken(self::SCOPES);
            $this->fail('Expected an ApiException');
        } catch (ApiException $e) {
            $lock = $this->locks($repo)->lock($this->keyFor($manager) . ':lock', 10);
            $this->assertTrue($lock->get(), 'The refresh lock must be released after a failure');
        }
    }

    public function test_invalid_token_response_is_rejected_and_not_cached(): void
    {
        $repo = $this->repository();
        $oauth = $this->oauth();
        $oauth->rawResponse = '{"success":false,"message":"nope","token":null}';
        $manager = new TokenManager($oauth, $repo);

        try {
            $manager->getToken(self::SCOPES);
            $this->fail('Expected an ApiException');
        } catch (ApiException $e) {
            $this->assertNull($repo->get($this->keyFor($manager)));
        }
    }

    public function test_waiting_for_another_worker_times_out_when_no_token_appears(): void
    {
        $repo = $this->repository();
        $oauth = $this->oauth();
        $manager = new TokenManager($oauth, $repo, lockWait: 1);
        $this->holdLock($repo, $manager);

        $this->expectException(ApiException::class);
        try {
            $manager->getToken(self::SCOPES);
        } finally {
            $this->assertSame(0, $oauth->calls);
        }
    }

    public function test_waiting_worker_gets_the_lock_once_the_holder_lets_go(): void
    {
        $repo = $this->repository();
        $oauth = $this->oauth();
        $manager = new TokenManager($oauth, $repo, lockWait: 5);
        $this->holdLock($repo, $manager, 1);

        $this->assertSame('tok-1', $manager->getToken(self::SCOPES));
        $this->assertSame(1, $oauth->calls);
    }

    public function test_token_stored_by_another_worker_during_the_lock_wait_is_reused(): void
    {
        $repo = new StaleFirstReadRepository(new ArrayStore());
        $first = $this->oauth();
        (new TokenManager($first, $repo))->getToken(self::SCOPES);

        $repo->staleView = ['token' => 'old', 'expires_at' => time() + 100, 'refresh_at' => time() - 1];
        $second = $this->oauth();

        $this->assertSame('tok-1', (new TokenManager($second, $repo))->getToken(self::SCOPES));
        $this->assertSame(0, $second->calls, 'The double-check inside the lock must avoid a second request');
    }

    public function test_cache_store_without_lock_support_is_rejected(): void
    {
        $repository = $this->createMock(CacheRepository::class);
        $repository->method('getStore')->willReturn($this->createMock(Store::class));

        $this->expectException(\InvalidArgumentException::class);
        new TokenManager($this->oauth(), $repository);
    }

    public function test_ttl_must_be_positive(): void
    {
        $manager = new TokenManager($this->oauth(), $this->repository());

        $this->expectException(\InvalidArgumentException::class);
        $manager->getToken(self::SCOPES, 0);
    }
}
