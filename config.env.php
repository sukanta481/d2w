<?php
/**
 * Environment Configuration Loader
 * Reads .env file if it exists, otherwise falls back to hardcoded values.
 */

function loadEnv($path = null) {
    $envFile = $path ?? __DIR__ . '/.env';

    if (!file_exists($envFile)) {
        return false;
    }

    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') continue;

        $parts = explode('=', $line, 2);
        if (count($parts) !== 2) continue;

        $key = trim($parts[0]);
        $value = trim($parts[1]);

        // Remove surrounding quotes
        if ((str_starts_with($value, '"') && str_ends_with($value, '"')) ||
            (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
            $value = substr($value, 1, -1);
        }

        $_ENV[$key] = $value;
        putenv("$key=$value");
    }

    return true;
}

// Load .env from project root
loadEnv(__DIR__ . '/.env');
