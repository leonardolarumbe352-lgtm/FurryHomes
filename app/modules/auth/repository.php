<?php

function auth_account_count() {
    return query('SELECT COUNT(*) FROM users');
}
