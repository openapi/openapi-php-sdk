<?php

namespace Openapi\Laravel;

use Illuminate\Support\ServiceProvider;
use Openapi\OauthClient;
use Openapi\TokenManager;

class OpenapiServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/openapi.php', 'openapi');

        $this->app->singleton(OauthClient::class, function ($app) {
            $config = $app['config']->get('openapi');

            return new OauthClient(
                $config['username'] ?? null,
                $config['apikey'] ?? null,
                (bool) ($config['test'] ?? false)
            );
        });

        $this->app->singleton(TokenManager::class, function ($app) {
            $config = $app['config']->get('openapi.token', []);

            return new TokenManager(
                $app->make(OauthClient::class),
                $app['cache']->store($config['store'] ?? null),
                $config['prefix'] ?? 'openapi:token:',
                (int) ($config['refresh_skew'] ?? 300),
                (int) ($config['lock_seconds'] ?? 45),
                (int) ($config['lock_wait'] ?? 15)
            );
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../config/openapi.php' => $this->app->configPath('openapi.php'),
            ], 'openapi-config');
        }
    }
}
