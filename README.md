# OpenAPI PHP SDK

A minimal and agnostic PHP SDK for OpenAPI, inspired by the Rust implementation. This SDK provides only the core HTTP primitives needed to interact with any OpenAPI service.

## Features

- **Agnostic Design**: No API-specific classes, works with any OpenAPI service
- **Minimal Dependencies**: Only requires PHP 8.0+ and cURL
- **OAuth Support**: Built-in OAuth client for token management  
- **HTTP Primitives**: GET, POST, PUT, DELETE, PATCH methods
- **Clean Interface**: Similar to the Rust SDK design

## Installation

```bash
composer require openapi/openapi-sdk
```

## Quick Start

### Token Generation

```php
use OpenApi\OauthClient;

$oauthClient = new OauthClient('username', 'apikey', true); // true for test environment

$scopes = [
    'GET:test.imprese.openapi.it/advance',
    'POST:test.postontarget.com/fields/country'
];

$result = $oauthClient->createToken($scopes, 3600);
$tokenData = json_decode($result, true);
$token = $tokenData['token'];
```

### Making API Calls

```php
use OpenApi\Client;

$client = new Client($token);

// GET request
$params = ['denominazione' => 'altravia', 'provincia' => 'RM'];
$response = $client->get('https://test.imprese.openapi.it/advance', $params);

// POST request  
$payload = ['limit' => 10, 'query' => ['country_code' => 'IT']];
$response = $client->post('https://test.postontarget.com/fields/country', $payload);

// Other HTTP methods
$response = $client->put($url, $payload);
$response = $client->delete($url);
$response = $client->patch($url, $payload);
```

## Architecture

This SDK follows a minimal approach with only essential components:

- `OauthClient`: Handles OAuth authentication and token management
- `Client`: Agnostic HTTP client for API calls
- `Exception`: Error handling
- `Cache\CacheInterface`: Optional caching interface

## Requirements

- PHP 8.0 or higher
- cURL extension
- JSON extension