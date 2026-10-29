<?php
require dirname(__DIR__) . '/app/config/database.php';
$cases = [
    [['SERVER_ADDR'=>'127.0.0.1'], 'apache2handler', 'database.local.php'],
    [['SERVER_ADDR'=>'::1'], 'apache2handler', 'database.local.php'],
    [[], 'cli', 'database.local.php'],
    [[], 'cli-server', 'database.local.php'],
    [['SERVER_ADDR'=>'203.0.113.1', 'HTTP_HOST'=>'localhost'], 'apache2handler', 'database.php'],
];
foreach ($cases as [$server, $sapi, $expected]) {
    if (basename(database_config_path('/project', $server, $sapi)) !== $expected) {
        throw new RuntimeException('Incorrect database configuration selected.');
    }
}
echo "PASS: Local, hosted, CLI, and spoofed Host configuration selection.\n";
