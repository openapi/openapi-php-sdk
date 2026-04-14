<?php

namespace Openapi\Environment\DotEnv;


/**
 * Minimal .env file loader for populating process and PHP runtime environment variables.
 *
 * This implementation reads key-value pairs from a .env file and hydrates
 * {@see $_ENV}, {@see $_SERVER}, and the process environment via {@see putenv()}.
 * Existing environment variables are preserved and are not overwritten.
 */
class OpenapiDotEnv
{
    /**
     * Absolute or relative path to the .env file.
     *
     * @var string
     */
    protected $path;

     /**
     * Creates a new DotEnv loader instance for the given file path.
     *
     * @param string $path Path to the .env file.
     *
     * @throws \InvalidArgumentException Thrown when the provided file does not exist.
     */
    public function __construct(string $path)
    {
        if(!file_exists($path)) {
            throw new \InvalidArgumentException(sprintf('%s does not exist', $path));
        }
        $this->path = $path;
    }

    /**
     * Loads environment variables from the configured .env file.
     *
     * Empty lines and comment lines are ignored. Only lines containing a key-value
     * separator are processed. Variables that already exist in {@see $_ENV},
     * {@see $_SERVER}, or the process environment are left untouched.
     *
     * @return void
     *
     * @throws \RuntimeException Thrown when the file is not readable.
     * @throws \RuntimeException Thrown when the file contents cannot be read.
     */
    public function load() :void
    {
        if (!is_readable($this->path)) {
            throw new \RuntimeException(sprintf('%s file is not readable', $this->path));
        }

        $lines = file($this->path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if ($lines === false) {
            throw new \RuntimeException(sprintf('Unable to read %s', $this->path));
        }

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '' || strpos($line, '#') === 0) {
                continue;
            }

            if (strpos($line, '=') === false) {
                continue;
            }

            [$name, $value] = explode('=', $line, 2);

            $name = trim($name);
            $value = $this->normalizeValue(trim($value));

            if ($name === '') {
                continue;
            }

            if ($this->isLoaded($name)) {
                continue;
            }

            putenv(sprintf('%s=%s', $name, $value));
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }

    /**
     * Determines whether the given environment variable is already available.
     *
     * A variable is considered loaded when it exists in {@see $_ENV},
     * {@see $_SERVER}, or the process environment.
     *
     * @param string $name Environment variable name.
     *
     * @return bool True when the variable is already present; otherwise false.
     */
    private function isLoaded(string $name): bool
    {
        return array_key_exists($name, $_ENV)
            || array_key_exists($name, $_SERVER)
            || getenv($name) !== false;
    }

    /**
     * Normalizes a parsed environment variable value.
     *
     * Matching single or double quotes wrapping the full value are removed.
     * All other values are returned unchanged.
     *
     * @param string $value Raw environment variable value.
     *
     * @return string Normalized environment variable value.
     */
    private function normalizeValue(string $value): string
    {
        $length = strlen($value);

        if ($length >= 2) {
            $first = $value[0];
            $last = $value[$length - 1];

            if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                return substr($value, 1, -1);
            }
        }

        return $value;
    }
}
