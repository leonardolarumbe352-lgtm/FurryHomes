<?php

function e($v): string {
    return htmlspecialchars((string)($v??''), ENT_QUOTES,'UTF-8');
}
function redirect(string $page): never {
    header('Location: index.php?page='.$page);
    exit;
}
function flash(string $message): void {
    $_SESSION['flash']=$message;
}
