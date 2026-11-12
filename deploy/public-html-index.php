<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

$project = dirname(__DIR__).'/serenovia';

if (is_file($maintenance = $project.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

require $project.'/vendor/autoload.php';

$app = require_once $project.'/bootstrap/app.php';
$app->handleRequest(Request::capture());
