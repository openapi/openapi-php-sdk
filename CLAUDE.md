# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Language Policy

**IMPORTANT:** The primary language for this codebase is English. All code, comments, documentation, commit messages, PR descriptions, and communications should be in English.

## Development Commands

### Testing
```bash
# Run PHPUnit tests
./vendor/bin/phpunit tests/

# Run specific test file
./vendor/bin/phpunit tests/ApiClientTest.php

# Note: Most integration tests are skipped by default as they require valid API tokens
```

### Dependencies
```bash
# Install Composer dependencies
composer install

# Install dev dependencies
composer install --dev
```

### Git Workflow
```bash
# Quick development push (defined in Makefile)
make dev-push
```

## Architecture Overview

This is a minimal, agnostic PHP SDK for Openapi services with only essential HTTP primitives. The architecture follows a clean, simple design inspired by the Rust implementation found in `reference/openapi-rust-sdk/`.

### Core Components

- **`OauthClient`** (`src/OauthClient.php`): Handles OAuth authentication and token management
  - Uses Basic Auth with username/apikey for authentication 
  - Supports both production (`oauth.openapi.it`) and test (`test.oauth.openapi.it`) environments
  - Methods: `getScopes()`, `createToken()`, `getTokens()`, `deleteToken()`, `getCounters()`

- **`Client`** (`src/Client.php`): Agnostic HTTP client for making API calls
  - Uses Bearer token authentication 
  - Supports GET, POST, PUT, DELETE, PATCH methods
  - Handles URL parameter encoding for GET requests
  - JSON payload encoding for POST/PUT/PATCH requests
  - 30-second timeout for all requests

- **`Exception`** (`src/Exception.php`): Custom exception handling for HTTP and cURL errors

- **Cache System** (`src/Cache/`): Optional caching interface with array-based implementation
  - `CacheInterface`: Contract for cache implementations
  - `ArrayCache`: In-memory cache implementation

- **`TokenManager`** (`src/TokenManager.php`): Laravel-only, opt-in OAuth token provider. Caches tokens in a Laravel cache store (`Illuminate\Contracts\Cache\Repository`), uses atomic locks to prevent stampedes between workers (Octane), serves a still-valid token while one worker refreshes (stale-while-revalidate), and takes the real expiry from the `expire` field of the `/token` response.
- **Laravel integration** (`src/Laravel/`, `config/openapi.php`): service provider (auto-discovered via `composer.json`) binding `OauthClient` and `TokenManager`; cache store configurable with `OPENAPI_CACHE_STORE` (default: the application's default store). `Client` and `OauthClient` remain framework-agnostic.

### Key Design Principles

1. **Agnostic**: No API-specific classes - works with any Openapi service
2. **Minimal**: Only core HTTP primitives, minimal dependencies (PHP 8.0+, cURL, JSON)
3. **Clean Interface**: Simple method signatures following REST conventions
4. **Environment Flexibility**: Built-in test/production environment switching

### Examples Usage Patterns

The `examples/` directory contains practical usage demonstrations:
- Token generation with OAuth client
- API calls with different HTTP methods
- Complete workflow integration

### PSR-4 Autoloading

The project uses PSR-4 autoloading with `Openapi\` namespace mapped to `src/` directory.

## Requirements

- PHP 8.0+
- cURL extension
- JSON extension
- PHPUnit 9.5+ for testing