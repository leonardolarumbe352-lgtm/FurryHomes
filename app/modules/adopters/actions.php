<?php

function adopters_add_contact(): void {
    if(!query("SELECT COUNT(*) FROM applications WHERE user_id=? AND status='Approved'",[required('user_id')])->fetchColumn())throw new RuntimeException('Choose an approved adopter.');
    query('INSERT INTO contacts(user_id,note,created_by) VALUES(?,?,?)',[required('user_id'),required('note'),user()['id']]);
}
