<?php

namespace OpenApi;

use OpenApi\Interfaces\HttpTransportInterface;
use OpenApi\Transports\CurlTransport;
use Psr\Http\Client\ClientInterface as PsrClientInterface;;


/**
 * Generic HTTP client for OpenAPI services
 * Handles REST operations with Bearer token authentication
 */
class Client
{
    private string $token;

    private HttpTransportInterface|PsrClientInterface $transport;

    /**
     * Initialize client with Bearer token
     */
    public function __construct(string $token, HttpTransportInterface|PsrClientInterface|null $transport = null)
    {
        $this->token = $token;
        $this->transport = $transport ?? new CurlTransport($token);
    }


      public function request(
        string $method,
        string $url,
        mixed $payload = null,
        ?array $params = null
    ): string {
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