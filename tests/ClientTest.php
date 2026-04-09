<?php

declare(strict_types=1);

namespace Tests;

use OpenApi\Client;
use Tests\Transports\FakeTransport;
use PHPUnit\Framework\TestCase;

final class ClientTest extends TestCase
{
    public function test_it_uses_injected_transport_for_requests(): void
    {
        $transport = new FakeTransport();

        $client = new Client('test-token', $transport);

        $response = $client->request(
            'POST',
            'https://example.com/api/users',
            ['name' => 'John'],
            ['page' => 1]
        );

        $this->assertSame('fake-response', $response);

        $this->assertSame('POST', $transport->lastMethod);
        $this->assertSame('https://example.com/api/users', $transport->lastUrl);
        $this->assertSame(['name' => 'John'], $transport->lastPayload);
        $this->assertSame(['page' => 1], $transport->lastParams);
        $this->assertSame(1, $transport->callCount);
    }

    public function test_it_calls_transport_once_per_request(): void
    {
        $transport = new FakeTransport();
        $client = new Client('test-token', $transport);

        $client->request('GET', 'https://example.com/one');
        $client->request('GET', 'https://example.com/two');

        $this->assertSame(2, $transport->callCount);
        $this->assertSame('https://example.com/two', $transport->lastUrl);
    }
}

