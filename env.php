<?php
/**
 * Minimal .env loader to make environment variables available in shared hosting
 * without adding extra dependencies.
 */
if (!function_exists('loadEnv')) {
    function loadEnv(array $candidateFiles = []): void
    {
        static $alreadyLoaded = false;
        if ($alreadyLoaded) {
            return;
        }
        $alreadyLoaded = true;

        // Default search order: repo root .env, public/.env, parent .env
        $defaults = [
            __DIR__ . '/.env',
            __DIR__ . '/public/.env',
            dirname(__DIR__) . '/.env',
        ];

        $files = array_filter(array_merge($candidateFiles, $defaults), 'is_readable');
        foreach ($files as $file) {
            foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
                if (str_starts_with(trim($line), '#')) {
                    continue;
                }
                if (!str_contains($line, '=')) {
                    continue;
                }
                [$key, $value] = array_map('trim', explode('=', $line, 2));
                // Strip optional quotes
                $value = trim($value, "\"'");
                putenv("$key=$value");
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
            }
            // stop at the first existing file to respect precedence
            return;
        }
    }
}
