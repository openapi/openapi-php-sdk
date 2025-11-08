<?php

use OpenApi\OauthClient;
use PHPUnit\Framework\TestCase;

final class OauthClientTest extends TestCase
{
    private string $username = 'test_username';
    private string $apikey = 'test_apikey';

    public function testOauthClientCreation(): void
    {
        $client = new OauthClient($this->username, $this->apikey, true);
        $this->assertInstanceOf(OauthClient::class, $client);
    }

    public function testOauthClientProductionMode(): void
    {
        $client = new OauthClient($this->username, $this->apikey, false);
        $this->assertInstanceOf(OauthClient::class, $client);
    }

    public function testCreateTokenWithScopes(): void
    {
        $this->markTestSkipped('Requires valid credentials for integration test');
        
        $client = new OauthClient($this->username, $this->apikey, true);
        $scopes = [
            'GET:test.imprese.openapi.it/advance',
            'POST:test.postontarget.com/fields/country'
        ];
        
        $result = $client->createToken($scopes, 3600);
        $this->assertIsString($result);
        
        $data = json_decode($result, true);
        $this->assertIsArray($data);
    }
}