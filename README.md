<div align="center">
  <a href="https://openapi.com/">
    <img alt="Openapi SDK for PHP" src=".github/assets/repo-header-a3.png" >
  </a>

  <h1>Openapi® client for PHP</h1>
  <h4>The perfect starting point to integrate <a href="https://openapi.com/">Openapi®</a> within your PHP project</h4>

[![Build Status](https://github.com/openapi/openapi-php-sdk/actions/workflows/php.yml/badge.svg)](https://github.com/openapi/openapi-php-sdk/actions/workflows/php.yml)
[![Packagist Version](https://img.shields.io/packagist/v/openapi/openapi-sdk)](https://packagist.org/packages/openapi/openapi-sdk)
[![PHP Version](https://img.shields.io/packagist/php-v/openapi/openapi-sdk)](https://packagist.org/packages/openapi/openapi-sdk)
[![License](https://img.shields.io/github/license/openapi/openapi-php-sdk?v=2)](LICENSE)
[![Downloads](https://img.shields.io/packagist/dt/openapi/openapi-sdk)](https://packagist.org/packages/openapi/openapi-sdk)
<br>
[![Linux Foundation Member](https://img.shields.io/badge/Linux%20Foundation-Silver%20Member-003778?logo=linux-foundation&logoColor=white)](https://www.linuxfoundation.org/about/members)

</div>

## Overview

A minimal and agnostic PHP SDK for Openapi, inspired by a clean client implementation. This SDK provides only the core HTTP primitives needed to interact with any Openapi service.

## Pre-requisites

Before using the Openapi PHP Client, you will need an account at [Openapi](https://console.openapi.com/) and an API key to the sandbox and/or production environment

## Features

- **Agnostic Design**: No API-specific classes, works with any Openapi service
- **Minimal Dependencies**: Only requires PHP 8.0+ and cURL
- **OAuth Support**: Built-in OAuth client for token management
- **HTTP Primitives**: GET, POST, PUT, DELETE, PATCH methods
- **Clean Interface**: Similar to the Rust SDK design
- **Static Analysis**: PHPStan level 6 configuration available via Composer

## What you can do

With the Openapi PHP Client, you can easily interact with a variety of services in the Openapi Marketplace. For example, you can:

- 📩 **Send SMS messages** with delivery reports and custom sender IDs
- 💸 **Process bills and payments** in real time via API
- 🧾 **Send electronic invoices** securely to the Italian Revenue Agency
- 📄 **Generate PDFs** from HTML content, including JavaScript rendering
- ✉️ **Manage certified emails** and legal communications via Italian Legalmail

For a complete list of all available services, check out the [Openapi Marketplace](https://console.openapi.com/) 🌐

## Installation

You can add the Openapi PHP Client to your project with the following command:

```bash
composer require openapi/openapi-sdk
```

## Quick Start

### Token Generation

```php
use Openapi\OauthClient;

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
use Openapi\Client;

$client = new Client($token);

// GET request
$params = ['denominazione' => 'Stellantis', 'provincia' => 'TO'];
$response = $client->get('https://test.company.openapi.com/IT-advanced', $params);

// POST request
$payload = ['limit' => 10, 'query' => ['country_code' => 'IT']];
$response = $client->post('https://test.postontarget.com/fields/country', $payload);

// Other HTTP methods
$response = $client->put($url, $payload);
$response = $client->delete($url);
$response = $client->patch($url, $payload);
```

## Shared token caching (Laravel Octane / multi-worker)

By default every call to `OauthClient::createToken()` sends a new request to the OAuth server. When many
processes do this at once (for example Laravel Octane/Swoole workers, each with its own memory), the OAuth
server can answer with HTTP 429.

`TokenManager` solves this by keeping the token in Laravel's cache, shared by all processes:

- A fresh token is read from the cache. No lock and no OAuth request.
- If no token exists, one worker takes an atomic lock (`Cache::lock()`) and requests it. The others wait, then read it from the cache.
- If a token is close to expiry, one worker refreshes it while the others keep using the still-valid token (stale-while-revalidate).
- If a refresh fails (for example with a 429) and the old token is still valid, the old token keeps being used.

> **Opt-in, Laravel only.** `TokenManager` needs Laravel's cache (`illuminate/cache`) and is not used
> automatically. `OauthClient` and `Client` work exactly as before and do not depend on Laravel, so plain PHP
> and PHP-FPM users are unaffected. Existing code that calls `createToken()` directly keeps the 429 problem until
> it switches to `TokenManager`.

### Setup

The package service provider (`Openapi\Laravel\OpenapiServiceProvider`) is auto-discovered. Optionally publish the config:

```bash
php artisan vendor:publish --tag=openapi-config
```

Set your credentials and a shared cache store in `.env`:

```dotenv
OPENAPI_OAUTH_USERNAME=...
OPENAPI_OAUTH_APIKEY=...
OPENAPI_OAUTH_TEST=false        # set to true to use the test OAuth server (required for test.* scopes)
OPENAPI_CACHE_STORE=redis       # recommended under Octane; empty = the application's default cache store
```

### Usage

```php
use Openapi\Client;
use Openapi\TokenManager;

$scopes = ['GET:test.imprese.openapi.it/advance'];   // test scope: needs OPENAPI_OAUTH_TEST=true

$token  = app(TokenManager::class)->getToken($scopes);
$client = new Client($token);
```

Outside the service provider you can build it yourself with any Laravel cache repository:
`new TokenManager($oauthClient, Cache::store('redis'))`.

#### Cache store requirements

Use a store that is shared between workers **and** supports atomic locks: `redis`, `memcached` or `database`.

- `array` is per-process, so each worker would have its own copy and the problem is not fixed.
- If `OPENAPI_CACHE_STORE` is empty, the application's default store is used. Make sure that default is a shared
  store. With `file` (single server only) or `array`, workers do not share tokens reliably, so set
  `OPENAPI_CACHE_STORE=redis` (or `memcached`) explicitly.
- A store without lock support is rejected with an `InvalidArgumentException` when `TokenManager` is created.

#### Octane: build the client per request

A `Client` holds the token it was created with. Do not keep one in an Octane singleton, or it will keep using that
token after it expires. Call `getToken()` on each request and create the `Client` from it.

#### Token lifetime

`getToken($scopes, $ttl = 3600)` asks the OAuth server for a token valid for `$ttl` seconds, but only when a new
token is needed. The expiry the server reports in the `expire` field (a unix timestamp) is what the cache uses, so a
token the server shortens is never served past its real expiry, and a token is never trusted for longer than the
`$ttl` you asked for. If the server sends no usable `expire`, `$ttl` is used. Keep the clocks of your servers
synchronised (NTP), since `expire` is compared with the local clock.

A cached token is reused whatever `$ttl` is passed: tokens are cached per OAuth URL, username and set of scopes
(scope order does not matter, and the API key is never part of the key).

The refresh margin (`refresh_skew`) is capped at half of the token's lifetime, so short-lived tokens are cached
and refreshed too, instead of being requested on every call.

#### Configuration

Options live under the `token` key of `config/openapi.php`:

| Option | Default | Description |
|---|---|---|
| `store` | `null` (`OPENAPI_CACHE_STORE`) | Cache store name. `null` uses the application's default store. |
| `prefix` | `openapi:token:` (`OPENAPI_CACHE_PREFIX`) | Prefix for cache keys. |
| `refresh_skew` | `300` | Seconds before expiry at which a token starts being refreshed (capped at half of the token's lifetime). |
| `lock_seconds` | `45` | Maximum lifetime of the lock (a safety net if a worker dies while holding it). Keep it above the OAuth request timeout (30 seconds). |
| `lock_wait` | `15` | Seconds a worker waits for another worker's token request when no token exists yet. If it times out, an `ApiException` is thrown. |

## Architecture

This SDK follows a minimal approach with only essential components:

- `OauthClient`: Handles OAuth authentication and token management
- `Client`: Agnostic HTTP client for API calls
- `Exception`: Error handling
- `Cache\CacheInterface`: Optional caching interface
- `TokenManager`: Laravel cache-backed, lock-protected token provider (opt-in)

## Requirements

- PHP 8.0 or higher
- cURL extension
- JSON extension

## Examples

You can find complete examples in the `examples/` directory:

- `examples/token_generation.php` - OAuth token generation example
- `examples/api_calls.php` - HTTP API calls example
- `examples/complete_workflow.php` - End-to-end workflow example

Run examples with:

```bash
composer run example:token
composer run example:api
composer run example:complete
```

## Testing

Run tests with:

```bash
# Run all tests
composer run test

# Run unit tests specifically
composer run test:unit
```

## Static Analysis

This SDK includes PHPStan as a Composer development dependency to help keep the codebase type-safe and maintainable.

PHPStan is configured in `phpstan.neon` and currently runs at level 6.

Run static analysis with:

```bash
composer run analyse
```

## Contributing

Contributions are always welcome! Whether you want to report bugs, suggest new features, improve documentation, or contribute code, your help is appreciated.

See [docs/contributing.md](docs/contributing.md) for detailed instructions on how to get started. Please make sure to follow this project's [docs/code-of-conduct.md](docs/code-of-conduct.md) to help maintain a welcoming and collaborative environment.

## Authors

Meet the project authors:

- L. Paderi ([@lpaderiAltravia](https://www.github.com/lpaderiAltravia))
- Openapi Team ([@openapi-it](https://github.com/openapi-it))

## Partners

Meet our partners using Openapi or contributing to this SDK:

- [Blank](https://www.blank.app/)
- [Credit Safe](https://www.creditsafe.com/)
- [Deliveroo](https://deliveroo.it/)
- [Gruppo MOL](https://molgroupitaly.it/it/)
- [Jakala](https://www.jakala.com/)
- [Octotelematics](https://www.octotelematics.com/)
- [OTOQI](https://otoqi.com/)
- [PWC](https://www.pwc.com/)
- [QOMODO S.R.L.](https://www.qomodo.me/)
- [SOUNDREEF S.P.A.](https://www.soundreef.com/)

## Our Commitments

We believe in open source and we act on that belief. We became Silver Members
of the Linux Foundation because we wanted to formally support the ecosystem
we build on every day. Open standards, open collaboration, and open governance
are part of how we work and how we think about software.

## License

This project is licensed under the [MIT License](LICENSE).

The MIT License is a permissive open-source license that allows you to freely use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the software, provided that the original copyright notice and this permission notice are included in all copies or substantial portions of the software.

In short, you are free to use this SDK in your personal, academic, or commercial projects, with minimal restrictions. The project is provided "as-is", without any warranty of any kind, either expressed or implied, including but not limited to the warranties of merchantability, fitness for a particular purpose, and non-infringement.

For more details, see the full license text at the [MIT License page](https://choosealicense.com/licenses/mit/).
