<?php
// Front controller: resolve a registered page, enforce access, then render it.
require __DIR__ . '/app/bootstrap.php';

$routes = require APP_PATH . '/config/routes.php';
$page = $_GET['page'] ?? (user() ? 'dashboard' : 'login');
if (!isset($routes[$page])) {
    $page = 'dashboard';
}
$route = $routes[$page];
$error = handle_post($page);

if (!user() && $route['roles'] !== []) {
    redirect('login');
}
if (user() && in_array($page, ['login', 'register'], true)) {
    redirect('dashboard');
}
if (!role_allowed($route['roles'])) {
    http_response_code(403);
    exit('Access denied.');
}

if (isset($_GET['poll']) && user()) {
    require APP_PATH . '/modules/dashboard/updates.php';
    exit;
}

$repository = APP_PATH . '/modules/' . $route['module'] . '/repository.php';
if (is_file($repository)) {
    require_once $repository;
}
if ($page === 'analytics' && ($_GET['export'] ?? '') === 'csv') {
    require APP_PATH . '/modules/analytics/export.php';
    exit;
}

$titles = array_column($routes, 'title', null);
$titles = array_combine(array_keys($routes), $titles);
$petOptions = [];
if (in_array($page, ['medical'], true)) {
    foreach (rows('SELECT id,name FROM pets ORDER BY name') as $pet) {
        $petOptions[$pet['id']] = $pet['name'];
    }
}
require APP_PATH . '/layouts/main.php';
