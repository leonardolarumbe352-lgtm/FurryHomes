<?php

function role_allowed(array $roles): bool {
    return $roles === [] || in_array(user()['role'] ?? '', $roles, true);
}
function dispatch_action(string $action): void {
    $actions = require APP_PATH . '/config/actions.php';
    if (!isset($actions[$action])) {
        throw new RuntimeException('Unknown action.');
    }
    [$module, $handler, $roles] = $actions[$action];
    if (!role_allowed($roles)) {
        throw new RuntimeException('You do not have permission to perform this action.');
    }
    require_once APP_PATH . '/modules/' . $module . '/actions.php';
    if (in_array($action, ['login', 'register'], true)) {
        $handler($action);
    }
    else {
        $handler();
    }
}
function handle_post(string $page): ?string {
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        return null;
    }
    try {
        if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
            throw new RuntimeException('Your session expired. Refresh and try again.');
        }
        dispatch_action(required('action'));
        flash('Saved successfully.');
        redirect($page);
    }
    catch (Throwable $error) {
        if ($error instanceof PDOException) {
            return str_contains($error->getMessage(), 'UNIQUE') || ($error->errorInfo[1] ?? null) === 1062 ? 'That record already exists. You may have already submitted this application.' : 'The record could not be saved. Check your selections and try again.';
        }
        return $error->getMessage();
    }
}
