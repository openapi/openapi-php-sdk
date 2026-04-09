<?php
namespace Tests\Transports;

use OpenApi\Interfaces\HttpTransportInterface;


final class FakeTransport implements HttpTransportInterface
{
    public ?string $lastMethod = null;
    public ?string $lastUrl = null;
    public mixed $lastPayload = null;
    public ?array $lastParams = null;
    public int $callCount = 0;

    public function request(
        string $method,
        string $url,
        mixed $payload = null,
        ?array $params = null
    ): string {
        $this->callCount++;
        $this->lastMethod = $method;
        $this->lastUrl = $url;
        $this->lastPayload = $payload;
        $this->lastParams = $params;

        return 'fake-response';
    }
}