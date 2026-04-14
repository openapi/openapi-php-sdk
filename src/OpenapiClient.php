<?php

namespace Openapi;

use Openapi\Interfaces\OpenapiHttpTransportInterface;
use Openapi\Transports\OpenapiCurlTransport;
use Psr\Http\Client\ClientInterface as PsrClientInterface;;


/**
 * Generic HTTP client for Openapi services
 * Handles REST operations with Bearer token authentication
 */
class OpenapiClient
{
    private string $token;

    private OpenapiHttpTransportInterface|PsrClientInterface $transport;

    private ?string $baseUrl = null;

    /**
     * Initialize client with Bearer token
     */
    public function __construct(?string $token = null, OpenapiHttpTransportInterface|PsrClientInterface|null $transport = null)
    {
        $this->token = $token ?? getenv('OPEN_API_TOKEN') ;
        if(getenv("OPENAPI_BASE_URL")){
            $this->baseUrl = getenv("OPENAPI_BASE_URL");
        }
        $this->transport = $transport ?? new OpenapiCurlTransport($token);
    }


      public function request(
        string $method,
        string $url,
        mixed $payload = null,
        ?array $params = null
    ): string {
        $isAbsolute = str_starts_with(strtolower($url), 'http');

        // Wenn nicht absolut und baseUrl vorhanden -> Zusammenfügen
        if (!$isAbsolute && !empty($this->baseUrl)) {
            $url = rtrim($this->baseUrl, '/') . '/' . ltrim($url, '/');
        }
        return $this->transport->request($method, $url, $payload, $params);
    }

    /**
     * Perform GET request
     */
    public function get(string $url, ?array $params = null): string
    {
        return $this->request('GET', $url, null, $params);
    }

    /**
     * Perform POST request
     */
    public function post(string $url, mixed $payload = null): string
    {
        return $this->request('POST', $url, $payload);
    }

    /**
     * Perform PUT request
     */
    public function put(string $url, mixed $payload = null): string
    {
        return $this->request('PUT', $url, $payload);
    }

    /**
     * Perform DELETE request
     */
    public function delete(string $url): string
    {
        return $this->request('DELETE', $url);
    }

    /**
     * Perform PATCH request
     */
    public function patch(string $url, mixed $payload = null): string
    {
        return $this->request('PATCH', $url, $payload);
    }
}
