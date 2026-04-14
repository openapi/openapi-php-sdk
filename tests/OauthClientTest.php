<?php

use Openapi\OpenapiOauthClient;
use PHPUnit\Framework\TestCase;

final class OpenapiOauthClientTest extends TestCase
{
    private string $username = 'test_username';
    private string $apikey = 'test_apikey';

    public function testOauthClientCreation(): void
    {
        $client = new OpenapiOauthClient($this->username, $this->apikey, true);
        $this->assertInstanceOf(OpenapiOauthClient::class, $client);
    }

    public function testOauthClientCanBeCreatedFromEnvironmentVariables(): void
    {
        $username = getenv('OPENAPI_USERNAME');
        $apikey = getenv('OPENAPI_SANDBOX_KEY');

        $this->assertNotFalse($username, 'OPENAPI_OAUTH_USERNAME is not set');
        $this->assertNotFalse($apikey, 'OPENAPI_OAUTH_APIKEY is not set');
        $this->assertNotSame('', $username, 'OPENAPI_OAUTH_USERNAME is empty');
        $this->assertNotSame('', $apikey, 'OPENAPI_OAUTH_APIKEY is empty');

        $client = new OpenapiOauthClient($username, $apikey, true);

        $this->assertInstanceOf(OpenapiOauthClient::class, $client);
    }

    public function testOauthClientProductionMode(): void
    {
        $client = new OpenapiOauthClient($this->username, $this->apikey, false);
        $this->assertInstanceOf(OpenapiOauthClient::class, $client);
    }

    public function testEnvironmentVariablesAreAvailable(): void
    {
        $this->assertSame('test_user', getenv('OPENAPI_USERNAME'));
        $this->assertSame('test_key', getenv('OPENAPI_SANDBOX_KEY'));
        $this->assertSame('https://api.com', getenv('OPENAPI_OAUTH_SANDBOX_URL'));
        $this->assertSame('https://api.com', getenv('OPENAPI_OAUTH_URL'));
        $this->assertSame('https://example.com', getenv('OPENAPI_BASE_URL'));
    }

    public function testCreateTokenWithScopes(): void
    {
        $this->markTestSkipped('Requires valid credentials for integration test');
        
        $client = new OpenapiOauthClient($this->username, $this->apikey, true);
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