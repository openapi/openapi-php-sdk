<?php

declare(strict_types=1);

use Openapi\Environment\DotEnv\OpenapiDotEnv;
use Composer\Autoload\ClassLoader;
use ReflectionClass;

/**
 * Determines whether the package should attempt to load its own .env file.
 *
 * If the host application already exposes common framework environment markers,
 * we assume environment bootstrapping has already happened and skip our fallback.
 *
 * @return bool True when the package should try to load its fallback .env file.
 */
if(!function_exists('shouldLoad')){
    function shouldLoad(): bool 
    {
        
        $frameworkVars = ['APP_ENV', 'APP_NAME', 'CI_ENVIRONMENT'];

        foreach ($frameworkVars as $var) {
            if (isset($_ENV[$var]) || isset($_SERVER[$var]) || getenv($var)) {
                return false; 
            }
        }

        return true;
    }
}

/**
 * Resolves the consumer project's root directory based on Composer's vendor path.
 *
 * This uses Composer's ClassLoader location to infer the vendor directory and then
 * derives the project root as the parent directory of vendor/.
 *
 * @return string|null The resolved project root when a .env file exists there; otherwise null.
 */
if(!function_exists('findProjectRoot')){
    function findProjectRoot(): ?string
    {
        if (!class_exists(ClassLoader::class)) {
            return null;
        }

        $reflection = new ReflectionClass(ClassLoader::class);
        $vendorDir = dirname($reflection->getFileName(), 2); 
        $projectRoot = dirname($vendorDir); 

        return is_file($projectRoot . DIRECTORY_SEPARATOR  . '.env') ? $projectRoot : null;
    }
}

$envFile =  findProjectRoot(__DIR__) . DIRECTORY_SEPARATOR . ".env";
if (is_file($envFile)) {
    (new OpenapiDotEnv($envFile))->load();
}