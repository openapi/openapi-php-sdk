<?php

namespace Openapi;

class OauthClient
{
    private string $url;
    private string $username;
    private string $apikey;

    const OAUTH_BASE_URL = 'https://oauth.openapi.it';
    const TEST_OAUTH_BASE_URL = 'https://test.oauth.openapi.it';

    public function __construct(?string $username = null, ?string $apikey = null, bool $test = false)
    {
        $this->username = $username ?? getenv('OPENAPI_OAUTH_USERNAME');
        $this->apikey = $apikey ?? getenv('OPENAPI_OAUTH_APIKEY');
        $this->url = $test
            ? (getenv('OPENAPI_OAUTH_TEST_URL') ?: self::TEST_OAUTH_BASE_URL)
            : (getenv('OPENAPI_OAUTH_URL') ?: self::OAUTH_BASE_URL);
    }

    public function getScopes(bool $limit = false): string
    {
        $params = ['limit' => $limit ? 1 : 0];
        $url = $this->url . '/scopes?' . http_build_query($params);

        return $this->request('GET', $url);
    }

    /**
     * @param list<string> $scopes
     */
    public function createToken(array $scopes, int $ttl = 3600): string
    {
        $body = [
            'scopes' => $scopes,
            'ttl' => $ttl
        ];

        return $this->request('POST', $this->url . '/token', $body);
    }

    public function getTokens(string $scope): string
    {
        $params = ['scope' => $scope];
        $url = $this->url . '/token?' . http_build_query($params);

        return $this->request('GET', $url);
    }

    public function deleteToken(string $id): string
    {
        return $this->request('DELETE', $this->url . '/token/' . $id);
    }

    public function getCounters(string $period, string $date): string
    {
        return $this->request('GET', $this->url . '/counters/' . $period . '/' . $date);
    }

    
    /**
     * @param array<string, mixed>|null $body
     */
    private function request(string $method, string $url, ?array $body = null): string
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
            throw new ApiException("cURL Error: " . $error);
        }

        // TODO: Parse response body and provide structured error details with auth-specific hints
        if ($httpCode >= 400) {
            throw new ApiException("HTTP Error {$httpCode}: " . $response);
        }

        return $response;
    }
}
