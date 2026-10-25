<?php
// Safe routing for PHP's built-in development server (.htaccess is Apache-only).
$path=rawurldecode(parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH)??'/');
if($path==='/' || $path==='/index.php'){require __DIR__.'/index.php';return true;}
$file=realpath(__DIR__.$path);
$public=realpath(__DIR__.'/public').DIRECTORY_SEPARATOR;
if($file && str_starts_with($file,$public) && is_file($file) && in_array(strtolower(pathinfo($file,PATHINFO_EXTENSION)),['css','js','jpg','jpeg','png','webp'],true))return false;
http_response_code(404);echo 'Not found';
