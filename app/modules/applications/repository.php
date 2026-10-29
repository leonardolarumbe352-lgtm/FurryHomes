<?php

function applications_available_pet(int $id) {
    return query("SELECT * FROM pets WHERE id=? AND status IN ('Available','In foster')",[$id]);
}
function applications_listing() {
    return rows('SELECT a.*,p.name pet,u.name applicant,u.email,u.phone FROM applications a JOIN pets p ON p.id=a.pet_id JOIN users u ON u.id=a.user_id'.(staff()?'':' WHERE a.user_id='.(int)user()['id']).' ORDER BY a.id DESC');
}
