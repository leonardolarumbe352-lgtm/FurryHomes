<?php

function pets_save(): void {
    $id=(int)($_POST['id']??0);
    $p=$id?query('SELECT * FROM pets WHERE id=?',[$id])->fetch(PDO::FETCH_ASSOC):null;
    $status=choice('status',['Available','In foster','Adopted','On hold']);
    if($status==='Adopted' && (!$p || $p['status']!=='Adopted'))throw new RuntimeException('Finalize an adoption contract to mark a pet adopted.');
    if($p && $p['status']==='Adopted' && $status!=='Adopted')throw new RuntimeException('An adopted pet has a finalized contract.');
    $active=$id?query('SELECT COUNT(*) FROM placements WHERE pet_id=? AND end_date IS NULL',[$id])->fetchColumn():0;
    if(($status==='In foster') !== ((int)$active>0))throw new RuntimeException('Manage foster status in the Foster homes module.');
    $values=[required('name'),choice('species',['Dog','Cat','Other']),required('breed'),required('age'),choice('gender',['Female','Male','Unknown']),$status,required('description'),photo()??($p['photo']??null),required('intake_date')];
    if($id){
        $values[]=$id;
        query('UPDATE pets SET name=?,species=?,breed=?,age=?,gender=?,status=?,description=?,photo=?,intake_date=? WHERE id=?',$values);
    }
    else query('INSERT INTO pets(name,species,breed,age,gender,status,description,photo,intake_date) VALUES(?,?,?,?,?,?,?,?,?)',$values);
}
