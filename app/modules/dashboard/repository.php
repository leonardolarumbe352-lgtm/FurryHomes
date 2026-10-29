<?php

function dashboard_care_count() {
    return query("SELECT COUNT(*) FROM pets WHERE status<>'Adopted'");
}
function dashboard_adoption_count() {
    return query('SELECT COUNT(*) FROM contracts');
}
function dashboard_application_count() {
    return query('SELECT COUNT(*) FROM applications'.(staff()?'':' WHERE user_id='.(int)user()['id']));
}
function dashboard_foster_count() {
    return query('SELECT COUNT(*) FROM placements WHERE end_date IS NULL');
}
function dashboard_notifications() {
    return rows('SELECT * FROM notifications WHERE user_id=? ORDER BY id DESC LIMIT 10',[user()['id']]);
}
