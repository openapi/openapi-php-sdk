<?php

namespace Tests\Transports;

use Openapi\Interfaces\HttpTransportInterface;

final class FakeTransport implements HttpTransportInterface
{
    public ?string $lastMethod = null;
    public ?string $lastUrl = null;
    public mixed $lastPayload = null;
     /**
     * @var array<string, mixed>|string|null
     */
    public array|string|null $lastParams = null;
    public int $callCount = 0;

    public function request(
        string $method,
        string $url,
        mixed $payload = null,
        array|string|null $params = null
    ): string {
        $this->callCount++;
        $this->lastMethod = $method;
        $this->lastUrl = $url;
        $this->lastPayload = $payload;
        $this->lastParams = $params;

        return 'fake-response';
    }
}
