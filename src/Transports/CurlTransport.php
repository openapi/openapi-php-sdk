<?php

namespace Openapi\Transports;

use Openapi\Interfaces\HttpTransportInterface;

final class CurlTransport implements HttpTransportInterface
{
    public function __construct(
        private ?string $token = null
    ) {
    }

    public function request(
        string $method,
        string $url,
        mixed $payload = null,
        array|string|null $params = null
    ): string {
        if ($params && $method === 'GET') {
            $url .= '?' . http_build_query($params);
        }

        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->token,
            ],
        ]);

        if ($payload && in_array($method, ['POST', 'PUT', 'PATCH'], true)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, is_string($payload) ? $payload : json_encode($payload));
        }

        if ($params && $method !== 'GET' && !$payload) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, is_string($params) ? $params : http_build_query($params));
        }

        $response = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false) {
            throw new \RuntimeException('cURL error: ' . $error);
        }

        if ($httpCode >= 400) {
            throw new \RuntimeException("HTTP error {$httpCode}: {$response}");
        }

        return $response;
    }
}
