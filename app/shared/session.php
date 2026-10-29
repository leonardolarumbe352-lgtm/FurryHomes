<?php

date_default_timezone_set('Asia/Singapore');
session_set_cookie_params(['httponly'=>true,'secure'=>!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off','samesite'=>'Lax']);
session_start();
$_SESSION['csrf'] ??= bin2hex(random_bytes(32));
