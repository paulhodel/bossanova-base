<?php

// Base folder outside public
chdir(__DIR__ . '/..');

if (file_exists('vendor/autoload.php')) {
    include 'vendor/autoload.php';
}

include 'config.php';

use bossanova\Jwt\Jwt;
use bossanova\Render\Render;
use bossanova\Translate\Translate;

// Translate based on any possible logged in user
$jwt = new Jwt;
$locale = isset($jwt->locale) && $jwt->locale ? $jwt->locale : 'en_GB';

Translate::start($locale);

// Run application
Render::run();
