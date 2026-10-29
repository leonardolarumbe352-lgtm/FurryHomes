<?php

function users_listing() {
    return rows('SELECT id,name,email,role FROM users ORDER BY name');
}
