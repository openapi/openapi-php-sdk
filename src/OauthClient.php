<?php

namespace OpenApi;

class OauthClient 
{
    private string $url;
    private string $username;
    private string $apikey;

    const OAUTH_BASE_URL = 'https://oauth.openapi.it';
    const TEST_OAUTH_BASE_URL = 'https://test.oauth.openapi.it';

    public function __construct(string $username, string $apikey, bool $test = false)
    {
        $this->username = $username;
        $this->apikey = $apikey;
        $this->url = $test ? self::TEST_OAUTH_BASE_URL : self::OAUTH_BASE_URL;
    }

    public function getScopes(bool $limit = false): string
    {
        $params = ['limit' => $limit ? 1 : 0];
        $url = $this->url . '/scopes?' . http_build_query($params);
        
        return $this->request('GET', $url);
    }

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

        if ($response === false) {
            throw new Exception("cURL Error: " . $error);
        }

        if ($httpCode >= 400) {
            throw new Exception("HTTP Error {$httpCode}: " . $response);
        }

        return $response;
    }
}