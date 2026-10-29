<?php

function medical_find(int $id) {
    return query('SELECT * FROM medical WHERE id=?',[$id]);
}
function medical_history() {
    return rows('SELECT m.*,p.name FROM medical m JOIN pets p ON p.id=m.pet_id ORDER BY record_date DESC,m.id DESC');
}
