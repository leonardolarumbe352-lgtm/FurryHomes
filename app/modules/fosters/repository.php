<?php

function fosters_available_pets() {
    return rows("SELECT id,name FROM pets WHERE status IN ('Available','On hold')");
}
function fosters_caregivers() {
    return rows('SELECT * FROM fosters');
}
function fosters_placements() {
    return rows('SELECT pl.*,p.name pet,f.name caregiver,f.address,f.phone FROM placements pl JOIN pets p ON p.id=pl.pet_id JOIN fosters f ON f.id=pl.foster_id ORDER BY pl.id DESC');
}
function fosters_directory() {
    return rows('SELECT * FROM fosters ORDER BY name');
}
