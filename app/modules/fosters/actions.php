<?php

function fosters_register(): void {
    query('INSERT INTO fosters(name,email,phone,address) VALUES(?,?,?,?)',[required('name'),required('email'),required('phone'),required('address')]);
}
function fosters_assign(): void {
    // Transaction 4: housing assignment and pet availability change together.
    transaction(function(){
        $id=required('pet_id');
        $status=query('SELECT status FROM pets WHERE id=?',[$id])->fetchColumn();
        if(!in_array($status,['Available','On hold'],true))throw new RuntimeException('Choose an available pet without an active placement.');
        query('INSERT INTO placements(pet_id,foster_id,start_date) VALUES(?,?,?)',[$id,required('foster_id'),required('start_date')]);
        query("UPDATE pets SET status='In foster' WHERE id=?",[$id]);
        audit('Placed pet #'.$id.' in foster care');
    });
}
function fosters_return_pet(): void {
    transaction(function(){
        $p=query('SELECT * FROM placements WHERE id=? AND end_date IS NULL',[required('id')])->fetch(PDO::FETCH_ASSOC);
        if(!$p)throw new RuntimeException('Placement already closed.');
        $end=required('end_date');
        if($end<$p['start_date'])throw new RuntimeException('End date must follow the start date.');
        query('UPDATE placements SET end_date=? WHERE id=?',[$end,$p['id']]);
        query("UPDATE pets SET status='Available' WHERE id=? AND status='In foster'",[$p['pet_id']]);
    });
}
