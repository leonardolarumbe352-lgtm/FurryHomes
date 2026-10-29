<?php

function adopters_search($search) {
    return rows("SELECT DISTINCT u.* FROM users u JOIN applications a ON a.user_id=u.id AND a.status='Approved' WHERE u.name LIKE ? OR u.email LIKE ? OR u.phone LIKE ? ORDER BY u.name",array_fill(0,3,'%'.$search.'%'));
}
function adopters_contacts($a) {
    return rows('SELECT c.*,u.name author FROM contacts c JOIN users u ON u.id=c.created_by WHERE c.user_id=? ORDER BY c.id DESC',[$a['id']]);
}
