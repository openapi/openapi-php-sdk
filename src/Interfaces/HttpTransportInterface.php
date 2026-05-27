<?php

namespace Openapi\Interfaces;

interface HttpTransportInterface
{
     /**
     * @param array<string, mixed>|string|null $params
     */
    public function request(
        string $method,
        string $url,
        mixed $payload = null,
        array|string|null $params = null
    ): string;
}
