<?php

namespace OpenApi;

/**
 * Generic HTTP client for OpenAPI services
 * Handles REST operations with Bearer token authentication
 */
class Client
{
    private string $token;

    /**
     * Initialize client with Bearer token
     */
    public function __construct(string $token)
    {
        $this->token = $token;
    }

    /**
     * Execute HTTP request
     *
     * @param string $method HTTP method (GET, POST, PUT, DELETE, PATCH)
     * @param string $url Target URL
     * @param mixed $payload Request body (for POST/PUT/PATCH)
     * @param array|null $params Query parameters (for GET) or form data (for other methods)
     * @return string Response body
     */
    public function request(string $method, string $url, mixed $payload = null, ?array $params = null): string
    {
        // Append query parameters for GET requests
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
                'Authorization: Bearer ' . $this->token
            ]
        ]);

        // Add JSON payload for POST/PUT/PATCH requests
        if ($payload && in_array($method, ['POST', 'PUT', 'PATCH'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, is_string($payload) ? $payload : json_encode($payload));
        }

        // Add form data for non-GET requests
        if ($params && $method !== 'GET') {
            curl_setopt($ch, CURLOPT_POSTFIELDS,
                is_string($params) ? $params : http_build_query($params));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        // TODO: Provide more graceful error message with connection context (timeout, DNS, SSL, etc.)
        if ($response === false) {
            throw new Exception("cURL Error: " . $error);
        }

        // TODO: Parse response body and provide structured error details (error code, message, request ID)
        if ($httpCode >= 400) {
            throw new Exception("HTTP Error {$httpCode}: " . $response);
        }

        return $response;
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