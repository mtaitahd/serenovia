<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

// XAMPP serves this project from /serenova while Laravel's front controller
// lives in /public. Normalize only rewritten, clean project URLs so Laravel
// treats /serenova as the base path instead of /serenova/public.
$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$normalizedScriptName = preg_replace('#/public/index\.php$#', '/index.php', $scriptName);

if ($normalizedScriptName !== $scriptName && ! str_contains($requestPath, '/public/')) {
    $_SERVER['SCRIPT_NAME'] = $normalizedScriptName;
    $_SERVER['PHP_SELF'] = $normalizedScriptName;
}

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
