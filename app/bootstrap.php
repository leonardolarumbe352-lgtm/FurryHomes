<?php

// Shared bootstrapping only. Feature code lives in app/modules/.
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', __DIR__);
require APP_PATH . '/shared/session.php';
require APP_PATH . '/config/database.php';
$config = require database_config_path(BASE_PATH, $_SERVER, PHP_SAPI);
$db = new PDO($config['dsn'], $config['user'], $config['password']);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
foreach (['database', 'auth', 'http', 'validation', 'activity', 'csrf', 'uploads', 'forms', 'dispatch'] as $component) {
    require_once APP_PATH . '/shared/' . $component . '.php';
}
