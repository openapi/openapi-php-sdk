<?php

namespace Openapi\Interfaces;

interface OpenapiHttpTransportInterface
{
    public function request(
        string $method,
        string $url,
        mixed $payload = null,
        ?array $params = null
    ): string;
}