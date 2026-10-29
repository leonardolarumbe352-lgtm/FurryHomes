<?php

function user(): ?array {
    return isset($_SESSION['uid']) ? (query('SELECT * FROM users WHERE id=?',[$_SESSION['uid']])->fetch(PDO::FETCH_ASSOC) ?: null) : null;
}
function staff(): bool {
    return in_array(user()['role']??'', ['Admin','Staff'],true);
}
