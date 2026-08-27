<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

if (file_exists($maintenance = '/home/ajmalaand/apps/poetry/storage/framework/maintenance.php')) {
    require $maintenance;
}

require '/home/ajmalaand/apps/poetry/vendor/autoload.php';

(require_once '/home/ajmalaand/apps/poetry/bootstrap/app.php')
    ->handleRequest(Request::capture());
