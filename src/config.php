<?php
// src/config.php

require_once '/var/www/html/vendor/autoload.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$logger = new Logger('auth_system');
$logger->pushHandler(new StreamHandler('/var/www/logs/app.log', Logger::DEBUG));

// Rendre le logger disponible globalement
global $logger;
