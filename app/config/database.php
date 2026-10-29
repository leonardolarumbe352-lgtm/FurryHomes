<?php

/** Choose by server address, never by the client-supplied Host header. */
function database_config_path(string $root, array $server, string $sapi): string
{
    $address = $server['SERVER_ADDR'] ?? '';
    $local = $sapi === 'cli' || $sapi === 'cli-server'
        || in_array($address, ['127.0.0.1', '::1', '::ffff:127.0.0.1'], true);
    return $root . '/storage/' . ($local ? 'database.local.php' : 'database.php');
}
