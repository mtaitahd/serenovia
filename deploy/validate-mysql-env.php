<?php
// Validate the staged environment before taking the current website offline.
require dirname(__DIR__).'/vendor/autoload.php';

try {
    $root = dirname(__DIR__);
    $current = Dotenv\Dotenv::parse(file_get_contents($root.'/.env'));
    $next = Dotenv\Dotenv::parse(file_get_contents($root.'/.env.mysql'));
    if (($current['DB_CONNECTION'] ?? 'mysql') !== 'sqlite') {
        throw new RuntimeException('Staged transfer only accepts an existing SQLite website. Remove .env.mysql for normal MySQL deployments.');
    }
    if (($next['DB_CONNECTION'] ?? '') !== 'mysql' || ! empty($next['DB_URL'])) {
        throw new RuntimeException('Set DB_CONNECTION=mysql and remove DB_URL from .env.mysql.');
    }
    foreach (['DB_HOST', 'DB_DATABASE', 'DB_USERNAME', 'APP_KEY', 'APP_URL'] as $key) {
        if (empty($next[$key])) {
            throw new RuntimeException("Missing {$key} in .env.mysql.");
        }
    }
    if (empty($current['APP_KEY']) || $current['APP_KEY'] !== $next['APP_KEY']) {
        throw new RuntimeException('Copy the existing APP_KEY unchanged into .env.mysql.');
    }
    if (($next['APP_ENV'] ?? '') !== 'production' || ($next['APP_DEBUG'] ?? '') !== 'false') {
        throw new RuntimeException('Staged server environment must use APP_ENV=production and APP_DEBUG=false.');
    }
    // Source path is fixed by this deployment layout; reject a different active file.
    $source = $current['DB_DATABASE'] ?? $root.'/database/database.sqlite';
    if (realpath($source) !== realpath($root.'/database/database.sqlite')) {
        throw new RuntimeException('Active SQLite path differs from database/database.sqlite. Resolve it before transfer.');
    }
    fwrite(STDOUT, "Staged MySQL environment validated.\n");
} catch (Throwable $error) {
    fwrite(STDERR, "MySQL preparation: ".$error->getMessage()."\n");
    exit(1);
}
