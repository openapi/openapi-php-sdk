<?php

use OpenApi\Client;
use PHPUnit\Framework\TestCase;

final class ApiClientTest extends TestCase
{
    private string $testToken = 'test_token_string';

    public function testClientCreation(): void
    {
        $client = new Client($this->testToken);
        $this->assertInstanceOf(Client::class, $client);
    }

    public function testGetRequest(): void
    {
        $this->markTestSkipped('Requires valid token for integration test');
        
        $client = new Client($this->testToken);
        $params = [
            'denominazione' => 'altravia',
            'provincia' => 'RM',
            'codice_ateco' => '6201'
        ];
        
        $result = $client->get('https://test.imprese.openapi.it/advance', $params);
        $this->assertIsString($result);
    }

    public function testPostRequest(): void
    {
        $this->markTestSkipped('Requires valid token for integration test');
        
        $client = new Client($this->testToken);
        $payload = [
            'limit' => 10,
            'query' => [
                'country_code' => 'IT'
            ]
        ];
        
        $result = $client->post('https://test.postontarget.com/fields/country', $payload);
        $this->assertIsString($result);
    }

    public function testPutRequest(): void
    {
        $this->markTestSkipped('Requires valid token for integration test');
        
        $client = new Client($this->testToken);
        $payload = ['test' => 'data'];
        
        $result = $client->put('https://example.com/api', $payload);
        $this->assertIsString($result);
    }

    public function testDeleteRequest(): void
    {
        $this->markTestSkipped('Requires valid token for integration test');
        
        $client = new Client($this->testToken);
        
        $result = $client->delete('https://example.com/api/123');
        $this->assertIsString($result);
    }

    public function testPatchRequest(): void
    {
        $this->markTestSkipped('Requires valid token for integration test');
        
        $client = new Client($this->testToken);
        $payload = ['update' => 'data'];
        
        $result = $client->patch('https://example.com/api/123', $payload);
        $this->assertIsString($result);
    }
}