<?php

function contracts_approved_applications() {
    return rows("SELECT a.id,p.name,u.name adopter FROM applications a JOIN pets p ON p.id=a.pet_id JOIN users u ON u.id=a.user_id WHERE a.status='Approved' AND p.status<>'Adopted' AND NOT EXISTS(SELECT 1 FROM contracts c WHERE c.application_id=a.id)");
}
function contracts_listing() {
    return rows('SELECT c.*,p.name pet,u.name adopter FROM contracts c JOIN applications a ON a.id=c.application_id JOIN pets p ON p.id=a.pet_id JOIN users u ON u.id=a.user_id ORDER BY c.id DESC');
}
