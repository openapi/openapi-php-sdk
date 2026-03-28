<?php

namespace OpenApi\Interfaces;

interface HttpTransportInterface
{
    public function request(
        string $method,
        string $url,
        mixed $payload = null,
        ?array $params = null
    ): string;
}