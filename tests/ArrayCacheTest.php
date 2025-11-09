<?php

use OpenApi\Cache\ArrayCache;
use PHPUnit\Framework\TestCase;

final class ArrayCacheTest extends TestCase
{
    private ArrayCache $cache;

    protected function setUp(): void
    {
        $this->cache = new ArrayCache();
    }

    public function testCacheImplementation(): void
    {
        $key = 'test_key';
        $value = 'test_value';

        // Test save and get
        $this->assertTrue($this->cache->save($key, $value));
        $this->assertEquals($value, $this->cache->get($key));

        // Test delete
        $this->assertTrue($this->cache->delete($key));
        $this->assertNull($this->cache->get($key));
    }

    public function testCacheExpiry(): void
    {
        $key = 'expiry_test';
        $value = 'expiry_value';
        
        // Save with 1 second TTL
        $this->cache->save($key, $value, 1);
        $this->assertEquals($value, $this->cache->get($key));
        
        // Wait for expiry and test
        sleep(2);
        $this->assertNull($this->cache->get($key));
    }

    public function testCacheClear(): void
    {
        $this->cache->save('key1', 'value1');
        $this->cache->save('key2', 'value2');

        $this->assertTrue($this->cache->clear());
        $this->assertNull($this->cache->get('key1'));
        $this->assertNull($this->cache->get('key2'));
    }

    public function testCacheWithComplexData(): void
    {
        $key = 'complex_key';
        $value = ['array' => 'data', 'number' => 42, 'nested' => ['key' => 'value']];

        $this->cache->save($key, $value);
        $this->assertEquals($value, $this->cache->get($key));
    }
}