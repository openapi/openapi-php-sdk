<?php

// phpcs:disable PSR1.Files.SideEffects.FoundWithSymbols

declare(strict_types=1);

use Openapi\Environment\DotEnv;
use Composer\Autoload\ClassLoader;

if (!function_exists('shouldLoad')) {
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

if (!function_exists('findProjectRoot')) {
    function findProjectRoot(): ?string
    {
        if (!class_exists(ClassLoader::class)) {
            return null;
        }

        $reflection = new \ReflectionClass(ClassLoader::class);
        $vendorDir = dirname($reflection->getFileName(), 2);
        $projectRoot = dirname($vendorDir);

        return is_file($projectRoot . DIRECTORY_SEPARATOR . '.env') ? $projectRoot : null;
    }
}

if (shouldLoad()) {
    $projectRoot = findProjectRoot();
    if ($projectRoot !== null) {
        (new DotEnv($projectRoot . DIRECTORY_SEPARATOR . '.env'))->load();
    }
}
