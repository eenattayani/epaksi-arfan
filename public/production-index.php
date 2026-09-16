<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
// 4 levels up: epaksi-arfan → public_html → ellokale.com → domains → home
require __DIR__.'/../../laravel-epaksi-arfan/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../../laravel-epaksi-arfan/bootstrap/app.php';

$app->handleRequest(Request::capture());