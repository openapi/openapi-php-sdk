<?php

namespace OpenApi;

/**
 * OAuth client for OpenAPI authentication
 * Handles token management using Basic Auth (username:apikey)
 */
class OauthClient
{
    private string $url;
    private string $username;
    private string $apikey;

    const OAUTH_BASE_URL = 'https://oauth.openapi.it';
    const TEST_OAUTH_BASE_URL = 'https://test.oauth.openapi.it';

    /**
     * Initialize OAuth client
     *
     * @param string $username API username
     * @param string $apikey API key
     * @param bool $test Use test environment if true
     */
    public function __construct(string $username, string $apikey, bool $test = false)
    {
        $this->username = $username;
        $this->apikey = $apikey;
        $this->url = $test ? self::TEST_OAUTH_BASE_URL : self::OAUTH_BASE_URL;
    }

    /**
     * Retrieve available scopes
     *
     * @param bool $limit Limit results if true
     * @return string JSON response with scopes
     */
    public function getScopes(bool $limit = false): string
    {
        $params = ['limit' => $limit ? 1 : 0];
        $url = $this->url . '/scopes?' . http_build_query($params);

        return $this->request('GET', $url);
    }

    /**
     * Create new access token
     *
     * @param array $scopes List of requested scopes
     * @param int $ttl Token time-to-live in seconds (default: 3600)
     * @return string JSON response with token details
     */
    public function createToken(array $scopes, int $ttl = 3600): string
    {
        $body = [
            'scopes' => $scopes,
            'ttl' => $ttl
        ];

        return $this->request('POST', $this->url . '/token', $body);
    }

    /**
     * Get tokens for specific scope
     *
     * @param string $scope Scope filter
     * @return string JSON response with matching tokens
     */
    public function getTokens(string $scope): string
    {
        $params = ['scope' => $scope];
        $url = $this->url . '/token?' . http_build_query($params);

        return $this->request('GET', $url);
    }

    /**
     * Delete token by ID
     *
     * @param string $id Token ID to delete
     * @return string JSON response
     */
    public function deleteToken(string $id): string
    {
        return $this->request('DELETE', $this->url . '/token/' . $id);
    }

    /**
     * Get usage counters
     *
     * @param string $period Time period (e.g., 'daily', 'monthly')
     * @param string $date Date in format YYYY-MM-DD
     * @return string JSON response with counter data
     */
    public function getCounters(string $period, string $date): string
    {
        return $this->request('GET', $this->url . '/counters/' . $period . '/' . $date);
    }

    /**
     * Execute HTTP request with Basic Auth
     */
    private function request(string $method, string $url, array $body = null): string
    {
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Basic ' . base64_encode($this->username . ':' . $this->apikey)
            ]
        ]);

        if ($body && in_array($method, ['POST', 'PUT'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        // TODO: Provide more graceful error message with connection context (timeout, DNS, SSL, etc.)
        if ($response === false) {
            throw new Exception("cURL Error: " . $error);
        }

        // TODO: Parse response body and provide structured error details with auth-specific hints (invalid credentials, expired key, etc.)
        if ($httpCode >= 400) {
            throw new Exception("HTTP Error {$httpCode}: " . $response);
        }

        return $response;
    }
}